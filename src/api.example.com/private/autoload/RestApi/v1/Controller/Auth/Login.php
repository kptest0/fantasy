<?php

namespace RestApi\Controller\Auth;

use RestApi\Model\User as Model;
use RestApi\View\Resource\Token;

use Nirvarnia\Contract\Helper\Password;
use Nirvarnia\Contract\Http\Message\Server\Request;
use Nirvarnia\Contract\Http\Message\Server\Response;
use Nirvarnia\Contract\Jwt\Encode as Jwt;
use Nirvarnia\Contract\Translate;

final class Login extends Controller
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

    public function index(Request $request, Response $response) : Response
    {
        $params = $this->extractParams($request);
        if ($params->empty()) {
            return $this->makeResponse($response, 'Missing Parameters');
        }

        $user = $this->findUser($params);
        if ( ! $user->valid()) {
            return $this->makeResponse($response, 'Invalid Login');
        }
        if ( ! $this->password->check($params->get('password'), $user->get('password_hash'))) {
            return $this->makeResponse($response, 'Invalid Login');
        }

        $token = $this->makeToken($user);
        return $this->makeResponse($response, 'OK', $token);
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
                     ->find();
        $this->model
             ->connection()->close();

        return $user;
    }

    private function makeResponse(Response $response, string $description, Token $token = null)
    {
        if ($description === 'Missing Parameters') {
            $response
                ->status()
                    ->code(400)->phrase('Bad Request');
            $response
                ->body()
                    ->notices()
                        ->add()->error('Missing Parameters');

            return $response;
        }

        if ($description === 'Invalid Login') {
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

        if ($description === 'Token' && $token) {
            $response
                ->status()
                    ->code(200)->phrase('OK');
            $response
                ->body()
                    ->success()
                    ->resource('tokens')
                        ->add($token);
        }

        throw new BadArgument('Unrecognized $description: "%s"', $description);
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
