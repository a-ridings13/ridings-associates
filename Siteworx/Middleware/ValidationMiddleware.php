<?php

declare(strict_types=1);

namespace Siteworx\Middleware;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Siteworx\Library\{Application\Core, Http\Request};

/**
 * Validation for Slim.
 */
class ValidationMiddleware extends Middleware
{

    /**
     * Validators.
     *
     * @var array
     */
    protected $validators = [];

    /**
     * Options.
     *
     * @var array
     */
    private $options = [
        'useTemplate' => false,
    ];

    /**
     * The translator to use fro the exception message.
     *
     * @var callable
     */
    protected $translator;

    /**
     * Errors from the validation.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * The 'errors' attribute name.
     *
     * @var string
     */
    public const ERRORS_NAME = 'errors';

    /**
     * The 'has_error' attribute name.
     *
     * @var string
     */
    public const HAS_ERRORS = 'has_errors';

    /**
     * The 'validators' attribute name.
     *
     * @var string
     */
    protected $validators_name = 'validators';

    /**
     * @param ServerRequestInterface|Request $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getParams() ?? [];

        if (\count($params) === 0) {
            return $handler->handle($request);
        }

        $callable = Core::getCallable($request);

        if ($callable === '') {
            return $handler->handle($request);
        }

        $signature = str_replace('Action', 'RequestSignature', $callable);
        [$class, $method] = explode(':', $signature);

        $signatureMethods = [
            'getRequestSignature',
            'postRequestSignature',
            'putRequestSignature',
            'deleteRequestSignature'
        ];

        $this->validators = $class::$method($request);

        if (\count($this->validators) === 0 || !\in_array($method, $signatureMethods, true)) {
            return $handler->handle($request);
        }

        $this->errors = [];

        $params = array_merge((array) $request->getAttribute('routeInfo')[2], $params);
        $this->validate($params, $this->validators);

        /** @var Request $request */
        $request = $request->withAttribute(self::ERRORS_NAME, $this->getErrors());
        $request = $request->withAttribute(self::HAS_ERRORS, $this->hasErrors());

        /** @var Request $request */
        $request = $request->withAttribute($this->validators_name, $this->getValidators());

        return $handler->handle($request);
    }

    /**
     * Validate the parameters by the given params, validators and actual keys.
     * This method populates the $errors attribute.
     *
     * @param array $params     The array of parameters.
     * @param array $validators The array of validators.
     * @param array $actualKeys An array that will save all the keys of the tree to retrieve the correct value.
     */
    private function validate($params = [], $validators = [], $actualKeys = []): void
    {
        //Validate every parameters in the validators array
        foreach ($validators as $key => $validator) {
            $actualKeys[] = $key;
            $param = $this->getNestedParam($params, $actualKeys);

            if (is_array($validator)) {
                $this->validate($params, $validator, $actualKeys);
            } else {
                try {
                    $validator->assert($param);
                } catch (NestedValidationException $exception) {
                    if ($this->translator) {
                        $exception->setParam('translator', $this->translator);
                    }

                    if ($this->options['useTemplate']) {
                        $this->errors[implode('.', $actualKeys)] = [$exception->getMainMessage()];
                    } else {
                        $this->errors[implode('.', $actualKeys)] = $exception->getMessages();
                    }
                }
            }

            //Remove the key added in this foreach
            array_pop($actualKeys);
        }
    }

    /**
     * Get the nested parameter value.
     *
     * @param array $params An array that represents the values of the parameters.
     * @param array $keys   An array that represents the tree of keys to use.
     *
     * @return mixed The nested parameter value by the given params and tree of keys.
     */
    private function getNestedParam($params = [], $keys = [])
    {
        if (empty($keys)) {
            return $params;
        }

        if ($params === null) {
            $params = [];
        }

        $firstKey = array_shift($keys);

        if (\array_key_exists($firstKey, $params) && $this->isArrayLike($params)) {
            $params = (array) $params;
            $paramValue = $params[$firstKey];

            return $this->getNestedParam($paramValue, $keys);
        }

        return null;
    }

    /**
     * Check if the given $params is an array like variable.
     *
     * @param array $params The variable to check.
     *
     * @return boolean Returns true if the given $params parameter is array like.
     */
    private function isArrayLike($params): bool
    {
        return is_array($params) || $params instanceof \SimpleXMLElement;
    }

    /**
     * Check if there are any errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get errors.
     *
     * @return array The errors array.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get validators.
     *
     * @return array The validators array.
     */
    public function getValidators(): array
    {
        return $this->validators;
    }
}
