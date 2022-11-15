<?php
namespace vk\lib\http;

require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/exception/HttpException.php';

use vk\lib\Context;
use vk\lib\http\exception\HttpException;


function build_request_from_globals(): Request
{
    $request_uri = \strtolower($_SERVER['REQUEST_URI'] ?? '');
    $path = $request_uri;
    $request_method = \strtolower($_SERVER['REQUEST_METHOD'] ?? 'GET');

    $qb = \strpos($request_uri, '?');
    if (false !== $qb) {
        $path = \substr($request_uri, 0, $qb);
    }

    $body = null;
    if ($request_method === 'post' || $request_method === 'put' || $request_method === 'patch') {
        $body = \trim(\file_get_contents('php://input'));
    }

    return new Request($path, $request_method, \getallheaders(), $body, $_GET);
}

function send_json_response($data = null, int $status = 200, string $content_type = 'application/json'): void
{
    header('Content-type: ' . $content_type);
    http_response_code($status);
    echo \json_encode($data, JSON_THROW_ON_ERROR);
    exit(0);
}

function send_response($data = '', int $status = 200, string $content_type = 'text/plain'): void
{
    header('Content-type: ' . $content_type);
    http_response_code($status);
    echo $data;
    exit(0);
}

function convert_and_response(Request $req, $result): void
{
    if (null === $result) {
        send_response('', 204);
    }
    if (\is_string($result) && empty(\trim($result))) {
        send_response();
    }

    $accept_header = $req->getHeaders()->getAccept();
    $response_content_type = $accept_header;
    $format = 'text';

    if (empty($accept_header) || '*/*' === $accept_header) {
        if (\strpos($req->getHeaders()->getContentType(), 'json') === true) {
            $format = 'json';
            $response_content_type = $req->getHeaders()->getContentType();
        }
    } elseif (\strpos($accept_header, 'json')) {
        $format = 'json';
    }

    if (\is_object($result)) {
        $result = object_to_array($result);
    }

    if (\is_array($result)) {
        switch ($format) {
            case 'json':
                send_json_response($result, 200, $response_content_type);
                break;
            case 'text':
                send_response(\print_r($result, true));
                break;
            default:
                send_response(
                    \sprintf(
                        'Not found response encoder for format=[%s], accept=%s',
                        $format,
                        \print_r($req->getHeaders()->getValues('accept'), true)
                    ),
                    500
                );
                break;
        }
    }

    if(\is_scalar($result)) {
        send_response($result, 200, $response_content_type);
    }
}

function serve_request(Request $req, string $routes, string $config): void
{
    $config = include $config;
    $routes = include $routes;

    // Ищем роут
    $handlers = $routes[$req->getMethod() . ':' . $req->getPath()] ?? null;

    if (null === $handlers) {
        send_response(
            \sprintf('Handler not found for `[%s] %s`', \strtoupper($req->getMethod()), $req->getPath()),
            500
        );
    }

    if (!\is_array($handlers)) {
        $handlers = [$handlers];
    }

    // middleware
    $handlers = \array_reverse($handlers);
    $action = static fn(Request $request, Context $context) => null; // Default response

    foreach ($handlers as $idx => $handler) {
        if (\is_string($handler)) {
            $handler = include $handler;
        }

        if (!\is_callable($handler)) {
            send_response(
                \sprintf('Bad handler for `[%s] %s` at index `%d`', \strtoupper($req->getMethod()), $req->getPath(), $idx),
                500
            );
        }

        $action = static fn(Request $request, Context $context) => $handler($request, $context, $action);
    }

    try {
        \ob_start();
        $result = $action($req, new Context(['config' => $config]));
        \ob_get_clean();
        convert_and_response($req, $result);
    } catch (HttpException $exception) {
        send_response($exception->getMessage(), $exception->getStatus());
    } catch (\Throwable $exception) {
        send_response($exception->getMessage(), 500);
    }
}

function object_to_array(object $object): array
{
    $clas_name = \get_class($object);
    $parent_class = \get_parent_class($object);
    $search = [$clas_name, '*'];
    if (false !== $parent_class) {
        $search[] = $parent_class;
    }

    $array = (array)$object;
    $result = [];
    foreach ($array as $k => $v) {
        $k = \trim(\str_replace($search, '', $k));
        if (\is_object($v)) {
            $result[$k] = object_to_array($v);
        } else {
            $result[$k] = $v;
        }
    }
    return $result;
}
