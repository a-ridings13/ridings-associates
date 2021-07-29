<?php

declare(strict_types=1);

namespace Siteworx\Library\Http;

use Slim\Psr7\{Cookies, Factory\StreamFactory, Factory\UriFactory, Headers, UploadedFile};

final class RequestFactory
{

    /**
     * Create new HTTP request with data extracted from the application
     * Environment object
     *
     * @param  Environment $environment The Slim application Environment
     *
     * @return Request
     */
    public static function createFromEnvironment(Environment $environment): Request
    {
        $method = $environment->get('REQUEST_METHOD');
        $uri = (new UriFactory())->createUri($environment->get('REQUEST_URI') ?? '');
        $headers = new Headers();
        $cookies = Cookies::parseHeader($headers->getHeader('Cookie'));
        $serverParams = $environment->all();
        $body = (new StreamFactory())->createStream();
        $uploadedFiles = UploadedFile::createFromGlobals($environment->get('FILES') ?? []);
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        if (
            $method === 'POST' &&
            in_array($request->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            // parsed body must be $_POST
            $request = $request->withParsedBody($_POST);
        }

        return $request;
    }

    /**
     * @return Request
     */
    public static function createFromGlobals(): Request
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = (new UriFactory())->createFromGlobals($_SERVER);

        $headers = Headers::createFromGlobals();
        $cookies = Cookies::parseHeader($headers->getHeader('Cookie'));

        $body = (new StreamFactory())->createStream();
        $body->write(file_get_contents('php://input'));
        $body->rewind();

        $uploadedFiles = UploadedFile::createFromGlobals($_SERVER);

        $request = new Request($method ?? 'GET', $uri, $headers, $cookies, $_SERVER, $body, $uploadedFiles);
        $contentTypes = $request->getHeader('Content-Type') ?? [];

        $parsedContentType = '';

        foreach ($contentTypes as $contentType) {
            $fragments = explode(';', $contentType);
            $parsedContentType = current($fragments);
        }

        $contentTypesWithParsedBodies = ['application/x-www-form-urlencoded', 'multipart/form-data'];

        if ($method === 'POST' && in_array($parsedContentType, $contentTypesWithParsedBodies, true)) {
            return $request->withParsedBody($_POST);
        }

        return $request;
    }
}
