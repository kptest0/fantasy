<?php

namespace RestApi\Controller\Uploads;

final class Files extends Controller
{
    public function index(Request $request, Response $response) : Response
    {
        if ( ! $request->body()->has('files')) {
            $response
                ->status()
                    ->code(400)->phrase('Bad Request');
            $response
                ->body()
                    ->notices()
                        ->add()->error('Missing Parameters');

            return $response;
        }

        $params = new Parameters([
            'files' => $request->body()->get('files')
        ]);

        $params->get('files')->forEach(function ($file) {
            $file->validate()
                 ->saveTo('dir/path')
                 ->rename('xxxx');
        });

        $response
            ->status()
                ->code(200)->phrase('OK');

        return $response;
    }
}
