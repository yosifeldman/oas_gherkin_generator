<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller;
use Nmi\Authjwt\Models\Roles;
use Nmi\Authjwt\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseController extends Controller
{
    /** @var \Monolog\Logger */
    protected $logger;

    /** @var User */
    protected $user;

    public static $messages = [
        'same'       => 'The :attribute and :other must match.',
        'size'       => 'The :attribute must be exactly :size.',
        'between'    => 'The :attribute value must be between :min - :max.',
        'in'         => 'The :attribute must be one of the following values: :values.',
        'required'   => 'The :attribute is required.',
        'string'     => 'The :attribute value must be a string.',
        'email'      => 'The :attribute must be a valid email.',
        'integer'    => 'The :attribute value must be an integer.',
        'unique'     => 'The :attribute :input already exists.',
        'money'      => 'The :attribute value must be in correct money format.',
        'alpha'      => 'The :attribute value must be entirely alphabetic characters.',
        'alpha_num'  => 'The :attribute value must be entirely alpha-numeric characters.',
        'alpha_dash' => 'The :attribute value must be alpha-numeric characters, dashes/underscores are allowed.',
        'uuid'       => 'The :attribute value is invalid.',
        'min'        => 'The :attribute value must be minimum :min.',
        'boolean'    => 'The :attribute value must be true, 1 or false, 0.',
        'percentage' => 'The :attribute value must be a percentage like 0.15.',
        'exists'     => 'The :attribute :input not found.',
        'digits'     => 'The :attribute must be exactly :digits digits.'
    ];

    public function __construct()
    {
        $this->user = Auth::user();
    }

    protected function success(array $responseData = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(['data' => $responseData], $status);
    }

    protected function created(array $entity): JsonResponse
    {
        return response()->json(['data' => $entity], Response::HTTP_CREATED);
    }

    public function deleted(): Response
    {
        return response(null, Response::HTTP_NO_CONTENT);
    }

    protected function error(string $errorMessage = 'Unexpected error', int $status = 500): JsonResponse
    {
        return response()->json(['error' => $errorMessage], $status);
    }

    protected function validationError(string $errorMessage = 'Your request was un-processable. Please check your input.'): void
    {
        abort(Response::HTTP_UNPROCESSABLE_ENTITY, $errorMessage);
    }

    /**
     * Apply query string filters
     *
     * Example:
     * ?lte[birth]=2010-01-01&gte[age]=21
     *
     * @param array   $params Query string params
     * @param Builder $qb     Model::query()
     *
     * @return Builder
     */
    public function applyFilters(array $params, Builder $qb): Builder
    {
        // available filters
        $filters = ['gt' => '>', 'lt' => '<', 'gte' => '>=', 'lte' => '<='];

        // remove non-filters
        unset($params['q'], $params['page'], $params['limit'], $params['order_by']);

        // apply
        foreach ($params as $col => $val) {
            $col = filter_var($col, FILTER_SANITIZE_STRING);
            if (\is_array($val) && isset($filters[$col])) {
                $op = $filters[$col];
                foreach ($val as $c => $v) {
                    $v = filter_var($v, FILTER_SANITIZE_STRING);
                    $c = str_replace(':', '.', $c);
                    $qb->where($c, $op, $v);
                }
            } else {
                $val = filter_var($val, FILTER_SANITIZE_STRING);
                $col = str_replace(':', '.', $col);
                $qb->where($col, '=', $val);
            }
        }

        return $qb;
    }

    public function addCartProducts(Request $request, Cart $cart): bool
    {
        // validate products
        $rules = ['products' => 'required|array'];
        foreach (Product::getRules() as $col => $rule) {
            $rules['products.*.' . $col] = $rule;
        }
        $input = $this->validate($request, $rules, static::$messages);

        // add new products or increase qty of the old ones
        if(!empty($input['products'])) {
            $key         = (new Product())->getKeyName();
            $oldProducts = $cart->products()->get()->keyBy($key);
            foreach ($input['products'] as $prod) {
                if (isset($oldProducts[$prod[$key]])) {
                    $tmp      = $oldProducts[$prod[$key]];
                    $tmp->qty += (int)$prod['qty'];
                } else {
                    $tmp = new Product($prod);
                }
                if (!$cart->products()->save($tmp)) {
                    return false;
                }
            }
        }

        return true;
    }
}