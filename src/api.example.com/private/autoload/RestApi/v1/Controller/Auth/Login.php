<?php

namespace RestApi\Controller\Auth;

use Nirvarnia\Contract\Http\Message\Server\Response  as HttpResponse;
use Nirvarnia\Contract\Http\Message\Server\Request   as HttpRequest;
use Nirvarnia\Contract\Jwt\Encode                    as Jwt;
use RestApi\Model\User                               as Model;
use Nirvarnia\Contract\Helper\Password               as Password;
use RestApi\View\Resource\Token                      as Token;
use Nirvarnia\Contract\Translate                     as Translate;

final class Login extends Controller
{
    private $jwt       = null;
    private $model     = null;
    private $password  = null;
    private $translate = null;

    public function __construct(
    Jwt       $jwt,
    Model     $model,
    Password  $password,
    Translate $translate)
    {
        $this->jwt       = $jwt;
        $this->model     = $model;
        $this->password  = $password;
        $this->translate = $translate;
    }

    public function index(HttpRequest $request, HttpResponse $response) : HttpResponse
    {
        $params = $this->extractParams($request);
        if ($params->empty()) {
            $response
                ->status()
                    ->code(400)->phrase('Bad Request');
            $response
                ->body()
                    ->notices()
                        ->add()->error('Missing Parameters');

            return $response;
        }

        $user = $this->findUser($params);
        if ( ! $user || ! $this->password->check($params->get('password'), $user->get('password_hash'))) {
            $response
                ->status()
                    ->code(200)->phrase('OK');
            $response
                ->body()
                    ->notices()
                        ->add()->error('Invalid Login')
                           ->explanation($this->translate('message: invalid username or password'));

            return $response;
        }

        $token = $this->makeToken($user);

        $response
            ->status()
                ->code(200)->phrase('OK');
        $response
            ->body()
                ->success()
                ->resource('tokens')
                    ->add($token);

        return $response;
    }

    private function extractParams(Request $request) : Parameters
    {
        $params = [];

        if ($request->body()->has('username', 'password')) {
            $params['username'] = $request->body()->get('username');
            $params['password'] = $request->body()->get('password');
        }

        return new Parameters($params);
    }

    private function findUser(Parameters $params) : User
    {
        $user = $this->model
                     ->where('username')->matches($params->get('username'))
                     ->find()->first();
        $this->model
             ->connection()->close();

        return $user;
    }

    private function makeToken(User $user) : Token
    {
        $token = $this->jwt
                     ->claims([
                         'id' => $user->get('id'),
                         'username' => $user->get('username'),
                     ])
                     ->encode();

        return new Token($token);
    }
}
