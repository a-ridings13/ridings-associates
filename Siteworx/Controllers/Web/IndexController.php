<?php

declare(strict_types=1);

namespace Siteworx\Controllers\Web;

use Siteworx\Controllers\Controller;
use Siteworx\Library\Http\{Request, Response};
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};

final class IndexController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getAction(Request $request, Response $response, array $params = []): Response
    {
        return $response->write(
            $this->view->render('Index')
        );
    }
}
