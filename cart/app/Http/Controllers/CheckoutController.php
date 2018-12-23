<?php

namespace App\Http\Controllers;

use App\Events\OrderPlacedStreamerEvent;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Helpers;
use App\Models\ServicesApi\Customers\Client as CustomersClient;
use App\Models\ServicesApi\Taxes\Client as TaxesClient;
use App\Models\ServicesApi\Products\Client as ProductsClient;
use function GuzzleHttp\Promise\unwrap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use \Prwnr\Streamer\Facades\Streamer;
use Ramsey\Uuid\Uuid;

class CheckoutController extends BaseController
{
    public function placeOrder(Request $request, Cart $cart): JsonResponse
    {
        // validation
        $rules = [
            'transactions'                => 'required|array',
            'transactions.*.type'         => 'required|in:payment,refund',
            'transactions.*.id'           => 'required|string',
            'transactions.*.method'       => 'required|string',
            'transactions.*.cc_type'      => 'required|string',
            'transactions.*.cc_last4'     => 'required|digits:4',
            'transactions.*.cc_exp_month' => 'required|digits:2',
            'transactions.*.cc_exp_year'  => 'required|digits:4',
            'transactions.*.cc_cid'       => 'required|numeric',
            'transactions.*.amount'       => 'required|money',
            'transactions.*.currency'     => 'required|string|size:3',
            'grand_total'                 => 'required|money',
            'tax'                         => 'required|percentage'
        ];
        $input = $this->validate($request, $rules, static::$messages);

        // check & fill customer info
        $c_client = new CustomersClient();
        $r        = $c_client->getCustomer($cart->customer_id);

        if (!$customer = $c_client->getArrayResponse($r)) {
            $this->validationError('Customer not found.');
        }

        // for now, just find any address
        $shipping_addr = null;
        foreach (array_get($customer, 'addresses', []) as $address) {
            if (!empty($address['type']) && $address['type'] === 'shipping') {
                $shipping_addr = $address;
                break;
            }
        }
        if (!$shipping_addr) {
            $this->validationError('Shipping address is missing.');
        }

        $cart->customer = $customer;

        // get taxes & products
        $taxClient    = (new TaxesClient())->async();
        $prodClient   = (new ProductsClient())->async();
        $cartProducts = $cart->products()->get()->keyBy('sku')->toArray();
        $prodIds      = array_keys($cartProducts);

        // prepare async promises
        $promises = [
            'taxes' => $taxClient->getTaxesForAddress($shipping_addr),
            //'products' => $prodClient->async()->getBrandProducts($cart->brand_id, $prodIds)
        ];

        // TODO: make only one request for all products when Products MS is ready
        foreach ($prodIds as $prodId) {
            $promises[$prodId] = $prodClient->getBrandProducts($cart->brand_id, $prodId);
        }

        // get async results
        $results = unwrap($promises);

        // check tax
        $taxesResp = $taxClient->getArrayResponse($results['taxes']);
        $taxes     = [];
        if ($rates = array_get($taxesResp, 'result')) {
            $tax_rate = null;
            foreach ($rates as $rate) {
                $tax_rate += ((float)$rate['rate']) / 100;
                $taxes[]  = [
                    'code' => $rate['code'],
                    'rate' => $rate['rate']
                ];
            }
            if ($tax_rate !== (float)$input['tax']) {
                $this->validationError('The taxes rate is wrong.');
            }
            $cart->taxes            = $taxes;
            $cart->taxes_total_rate = $tax_rate;
        } else {
            return $this->error('Failed to retrieve taxes.');
        }

        // check product prices
        foreach ($results as $label => $result) {
            if (\in_array($label, $prodIds, true)) {
                $product = $prodClient->getArrayResponse($result);
                $price   = (float)array_get($product, '0.price');
                if (Helpers::float_cmp($price, $cartProducts[$label]['price']) !== 0) {
                    $this->validationError("Product price is invalid for $label.");
                }
            }
        }

        // TODO: check CC verification

        // validate coupon/discount
        $coupon_code = object_get($cart, 'discount.coupon_code');
        if ($coupon_code && !Coupon::query()->first($coupon_code)) {
            $this->validationError("The order coupon doesn't exist.");
        }

        if (empty($cart->shipping)) {
            $this->validationError('The order shipping method is missing.');
        }

        // summarize all prices/discounts and calculate Grand Total
        $totals = $cart->getSummary();
        if (Helpers::float_cmp($totals['grand_total'], $request->input('grand_total')) !== 0) {
            $this->validationError('Total price is invalid. Operation failed.');
        }

        // validate transactions
        $trans_total = 0.00;
        foreach ($input['transactions'] as $transaction) {
            if ($transaction['type'] === 'refund') {
                $trans_total -= (float)$transaction['amount'];
            } else {
                $trans_total += (float)$transaction['amount'];
            }
        }
        if (Helpers::float_cmp($trans_total, $totals['grand_total']) !== 0) {
            $this->validationError('Transactions total amount is invalid. Please re-check the order');
        }
        $cart->transactions = $input['transactions'];

        // send place order command to event source
        $order_id = Uuid::uuid1()->toString();
        $evData   = array_merge($cart->getSummary(), ['customer' => $customer, 'order_id' => $order_id]);
        $event    = new OrderPlacedStreamerEvent($evData);
        if (Streamer::emit($event)) {
            $cart->delete();

            return $this->success(['order_id' => $order_id], Response::HTTP_ACCEPTED);
        }

        return $this->error('Unexpected error, please try later.');
    }
}