<?php

// See www.example.com for other settings

$app->route('*')
    ->via('Nirvarnia\Middleware\XApiKey->test')
    ->via('Nirvarnia\Middleware\ContentType->test')
    ->via('Nirvarnia\Middleware\NormalizeParameters->run');

$app->route('POST /onboarding/register')
    ->via('RestApi\Middleware\Onboarding\Register')
    ->to('RestApi\Controller\Onboarding\Register->index');

$app->route('POST /auth/login')
    ->via('RestApi\Middleware\Auth\Login')
    ->to('RestApi\Controller\Auth\Login')

$app->route('POST /auth/forgot-login')
    ->via('RestApi\Middleware\Auth\ForgotLogin')
    ->to('RestApi\Controller\Auth\ForgotLogin')

$app->route('POST /auth/reset-password')
    ->via('RestApi\Middleware\Auth\ResetPassword')
    ->to('RestApi\Controller\Auth\ResetPassword')

$app->route('GET /blog')
    ->to('RestApi\Controller\Blogs\ListBlogs');

$app->route('GET /blog/{blog-id}')
    ->to('RestApi\Controller\Blogs\GetBlog');

$app->route('POST /blog')
    ->to('RestApi\Controller\Blogs\CreateBlog');

$app->route('PUT /blog/{blog-id}')
    ->to('RestApi\Controller\Blogs\UpdateBlog');

$app->route('DELETE /blog/{blog-id}')
    ->to('RestApi\Controller\Blogs\DeleteBlog');

$app->route('GET /blog/{blog-id}/post')
    ->to('RestApi\Controller\Blogs\ListBlogPosts');

$app->route('GET /post')
    ->to('RestApi\Controller\Posts\ListPosts');

$app->route('GET /post/{post-id}')
    ->to('RestApi\Controller\Posts\GetPost');

$app->route('POST /post')
    ->to('RestApi\Controller\Posts\CreatePost');

$app->route('PUT /post/{post-id}')
    ->to('RestApi\Controller\Posts\UpdatePost');

$app->route('DELETE /post/{post-id}')
    ->to('RestApi\Controller\Posts\DeletePost');

$app->route('GET /post/{post-id}/attachment')
    ->to('RestApi\Controller\Posts\ListPostAttachments');

$app->route('POST /post/{post-id}/attachment')
    ->to('RestApi\Controller\Posts\CreatePostAttachment');

$app->route('PUT /post/{post-id}/attachment/{attachment-id}')
    ->to('RestApi\Controller\Posts\UpdatePostAttachment');

$app->route('DELETE /post/{post-id}/attachment/{attachment-id}')
    ->to('RestApi\Controller\Posts\DeletePostAttachment');

$app->route('GET /post/{post-id}/comment')
    ->to('RestApi\Controller\Posts\ListPostComments');

$app->route('POST /post/{post-id}/comment')
    ->to('RestApi\Controller\Posts\CreatePostComment');

$app->route('PUT /post/{post-id}/comment/{comment-id}')
    ->to('RestApi\Controller\Posts\UpdatePostComment');

$app->route('DELETE /post/{post-id}/comment/{comment-id}')
    ->to('RestApi\Controller\Posts\DeletePostComment');

$app->route('GET /post/{post-id}/history')
    ->to('RestApi\Controller\Posts\ListPostHistory');

$app->route(404)
    ->to('RestApi\Controller\NotFound');

$app->start();
