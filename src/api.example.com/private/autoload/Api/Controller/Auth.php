<?php

namespace App\Controller\Auth;

use App\Model\User as Model;
use App\View\Resource\Token;

use Nirvarnia\Contract\Helper\Password;
use Nirvarnia\Contract\Http\Message\Server\Request;
use Nirvarnia\Contract\Http\Message\Server\Response;
use Nirvarnia\Contract\Jwt\Encode as Jwt;
use Nirvarnia\Contract\Translate;

final class Auth extends Controller
{
    private $jwt       = null;
    private $model     = null;
    private $password  = null;
    private $translate = null;

    public function __construct(
    Jwt $jwt,
    Model $model,
    Password $password,
    Translate $translate)
    {
        $this->jwt       = $jwt;
        $this->model     = $model;
        $this->password  = $password;
        $this->translate = $translate;
    }

    public function login(Request $request, Response $response) : Response
    {
        if ( ! $request->body()->has('username', 'password')) {
            $response->status()
                ->code(400)->phrase('Bad Request');
            $response->body()
                ->notices()->add()
                ->error($this->translate('error_codes.missing_parameters'));

            return $response;
        }

        $response->status()
            ->code(200)->phrase('OK');

        $username = $request->body()->get('username');
        $password = $request->body()->get('password');

        $user = $this->model
            ->where('username')->matches($username)
            ->find()->first();

        $this->model
            ->connection()->close();

        if ( ! $user || $user->password !== $this->password->hash($password)) {
            $response->body()
                ->notices()->add()
                ->error($this->translate('error_codes.invalid_credentials'))
                ->message($this->translate('messages.invalid_username_or_password'));

            return $response;
        }

        $token = $this->jwt
            ->claims([
                'userid'   => $user->id,
                'username' => $user->username,
            ])
            ->encode();

        $response->body()
            ->success(true)
            ->resource('token')
                ->append(new Token($token));

        return $response;
    }
}
