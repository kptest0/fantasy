<?php

namespace RestApi\Controller;

use RestApi\Model\User as Model;
use RestApi\View\Resource\Token;

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
            $response
                ->status()
                    ->code(400)->phrase('Bad Request');
            $response
                ->body()
                    ->notices()
                        ->add()->error('Missing Parameters');

            return $response;
        }

        $response
            ->status()
                ->code(200)->phrase('OK');

        $input = (object) [
            'username' => $request->body()->get('username'),
            'password' => $request->body()->get('password')
        ];

        $user = $this->model
                     ->where('username')->matches($input->username)
                     ->find();
        $this->model
             ->connection()->close();

        if ( ! $user) {
            $response
                ->body()
                    ->notices()
                        ->add()->error('Invalid Credentials')
                           ->explanation($this->translate('message: invalid username or password'));

            return $response;
        }

        if ( ! $this->password->valid($input->password, $user->password_hash)) {
            $response
                ->body()
                    ->notices()
                        ->add()->error('Invalid Credentials')
                            ->explanation($this->translate('message: invalid password'));

            return $response;
        }

        $token = $this->jwt
                     ->claims([
                         'id' => $user->id,
                         'username' => $user->username,
                     ])
                     ->encode();

        $response
            ->body()
                ->success()
                ->resource('tokens')
                    ->add(new Token($token));

        return $response;
    }
}
