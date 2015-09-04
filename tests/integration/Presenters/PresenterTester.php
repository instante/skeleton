<?php

namespace Instante\Tests\Presenters;

use Nette\Application\IPresenterFactory;
use Nette\Application\IResponse as IAppResponse;
use Nette\Application\IRouter;
use Nette\Application\Routers\SimpleRouter;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\DI\Extensions\InjectExtension;
use Nette\FileNotFoundException;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Request as HttpRequest;
use Nette\Application\Request;
use Nette\Http\Response as HttpResponse;
use Nette\Http\Session;
use Nette\Http\Url;
use Nette\Http\UrlScript;
use Nette\InvalidStateException;
use Nette\Security\User;
use Nette\Http\FileUpload;

/**
 * TODO mock sessions
 * TODO mock user
 *
 */
class PresenterTester
{
    /** @var  Container */
    private $context;

    /** @var  Presenter */
    private $presenterCreator;

    /** @var  string */
    private $presenterName;

    /** @var bool when instantiated presenter is passed to constructor, this prevents tester from running it twice. */
    private $presenterAlreadyUsed = FALSE;

    /** @var array */
    private $dependencies = [];

    /** @var array */
    private $query = [];
    /** @var array */
    private $post = [];
    /** @var array */
    private $files = [];
    /** @var array */
    private $appRequestFlags = [];
    /** @var array */
    private $cookies = [];
    /** @var string */
    private $method = 'GET';
    /** @var array */
    private $headers = [];
    /** @var string */
    private $remoteAddress = '127.0.0.1';
    /** @var string */
    private $remoteHost = 'localhost';
    /** @var callable|NULL */
    private $rawBodyCallback = NULL;

    /** @var IPresenterFactory */
    private $presenterFactory = NULL;
    /** @var IRouter */
    private $router = NULL;
    /** @var IRequest */
    private $httpRequest = NULL;
    /** @var IResponse */
    private $httpResponse = NULL;
    /** @var Request */
    private $appRequest = NULL;
    /** @var Session  */
    private $session = NULL;
    /** @var User */
    private $user = NULL;
    /** @var ITemplateFactory */
    private $templateFactory = NULL;
    /** @var string */
    private $uploadTempDir;

    /**
     * @param Presenter|string|callable $presenterCreator
     * @param string $presenterName fully qualified presenter name (:Module:Module:Presenter)
     * @param Container $context
     * @param string|null $uploadTempDir
     */
    public function __construct($presenterCreator, $presenterName, Container $context, $uploadTempDir = NULL)
    {
        assert($presenterCreator instanceof Presenter || is_string($presenterCreator)
            || is_callable($presenterCreator));
        $this->presenterName = $presenterName;
        $this->presenterCreator = $presenterCreator;
        $this->context = $context;
        $this->uploadTempDir = $uploadTempDir ?: (isset($context->parameters['tempDir']) ? $context->parameters['tempDir'] : NULL);
        if ($this->uploadTempDir === NULL) {
            trigger_error('Temporary directory for uploaded files was not specified. Testing uploads will not be available.', E_USER_NOTICE);
        }
    }

    public function runPresenter()
    {
        $presenter = $this->createPresenter();

        $presenter->injectPrimary(
            $this->context,
            //handles link resolutions to other presenters; can be used from context
            $this->getPresenterFactory(),
            $this->getRouter(),
            $httpRequest = $this->getHttpRequest(),
            $httpResponse = $this->getHttpResponse(),

            $this->getSession(),
            $this->getUser(),
            $this->getTemplateFactory()
        );
        $this->doInjects($presenter);
        $presenter->autoCanonicalize = FALSE;

        //verify all @injects are injected
        $response = $presenter->run($this->getRequest());
        ob_start();
        // ini_set('disable_functions', 'exit, die'); - maybe for preventing dying responses
        $response->send($httpRequest, $httpResponse);
        $responseBody = ob_get_clean();
        return new TestResult($httpResponse, $responseBody, $response);
    }

    public function injectDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies + $this->dependencies;
        return $this;
    }

    private function doInjects(Presenter $presenter)
    {
        $properties = InjectExtension::getInjectProperties(get_class($presenter));
        foreach ($properties as $property => $type) {
            if (isset($this->dependencies['@' . $property])) {

                $presenter->$property = $this->dependencies['@' . $property];
            } elseif (isset($this->dependencies[$property])) {
                $presenter->$property = $this->dependencies[$property];
            }
        }

        $methods = InjectExtension::getInjectMethods($presenter);
        unset($methods['injectPrimary']);
        foreach (array_reverse($methods) as $method) {
            $injectName = lcfirst(substr($method, 6));
            if (isset($this->dependencies[$injectName])) {
                if (!is_array($this->dependencies[$injectName])) {
                    $this->dependencies[$injectName] = [$this->dependencies[$injectName]];
                }
                call_user_func_array($presenter->$method, $this->dependencies[$injectName]);
            }
        }

    }

    private function createPresenter()
    {
        $this->checkInstanceUsedOnce();
        if ($this->presenterCreator instanceof Presenter) {
            return $this->presenterCreator;
        } elseif (is_string($this->presenterCreator)) {
            return new $this->presenterCreator;
        } else {
            return call_user_func($this->presenterCreator);
        }
    }

    private function checkInstanceUsedOnce()
    {
        if ($this->presenterCreator instanceof Presenter) {
            if ($this->presenterAlreadyUsed) {
                throw new InvalidStateException('When passed instantiated presenter to ' . __CLASS__
                    . ', runPresenter() can be called only once');
            }
            $this->presenterAlreadyUsed = TRUE;
        }
    }

    /**
     * @param string $name original path to the file
     * @param string|NULL $tmpName path to tmp copy of the file; $name is copied to a temp file if tmpName is NULL
     * @param int $error upload error
     * @return FileUpload
     */
    public function createFileUpload($name, $tmpName = NULL, $error = UPLOAD_ERR_OK)
    {
        if ($error === UPLOAD_ERR_OK) {
            if (!file_exists($tmpName ?: $name)) {
                throw new FileNotFoundException('File passed to ' . __METHOD__ . ' has to exist when error is UPLOAD_ERR_OK');
            }
            if ($tmpName === NULL) {
                if ($this->uploadTempDir === NULL) {
                    throw new InvalidStateException('Temp dir for uploads was not configured, cannot test uploads');
                }
                $tmpName = tempnam($this->uploadTempDir, 'InstanteTestUpload');
                copy($name, $tmpName);
            }
        }
        return new FileUpload([
            'name' => basename($name),
            'size' => $error === UPLOAD_ERR_OK ? filesize($tmpName) : 0,
            'type' => 0, // dummy value as FileUpload only checks its presence :)
            'tmp_name' => $tmpName,
            'error' => $error,
        ]);
    }

    /**
     * @param string $key
     * @param string $name original path to the file
     * @param string|NULL $tmpName path to tmp copy of the file; $name is copied to a temp file if tmpName is NULL
     * @param int $error upload error
     * @param string|NULL $uploadedName
     * @return PresenterTester
     */
    public function addFileUpload($key, $name, $tmpName = NULL, $uploadedName = NULL, $error = UPLOAD_ERR_OK)
    {
        $this->addFiles([$key => $this->createFileUpload($name, $tmpName, $error, $uploadedName)]);
        return $this;
    }

    /**
     * @param string $key
     * @param string $name original path to the file
     * @param string|NULL $tmpName path to tmp copy of the file; $name is copied to a temp file if tmpName is NULL
     * @param int $error upload error
     * @param string|NULL $uploadedName
     * @return PresenterTester
     */
    public function addFailedFileUpload($key, $error = UPLOAD_ERR_NO_FILE)
    {
        $this->addFiles([$key => $this->createFileUpload(NULL, NULL, $error)]);
        return $this;
    }

    public function getTemplateFactory()
    {
        if ($this->templateFactory === NULL) {
            $this->templateFactory = $this->context->getByType('Nette\Application\UI\ITemplateFactory');
        }
        return $this->templateFactory;
    }

    public function getUser()
    {
        if ($this->user === NULL) {
            $this->user = $this->context->getByType('Nette\Security\User');
        }
        return $this->user;
    }

    public function getSession()
    {
        if ($this->session === NULL) {
            $this->session = $this->context->getByType('Nette\Http\Session');
        }
        return $this->session;
    }

    public function getHttpRequest()
    {
        if ($this->httpRequest === NULL) {
            $this->httpRequest = new HttpRequest( // request
                new UrlScript($this->getRouter()->constructUrl($this->getRequest(), new Url('http://instante.test/'))),
                NULL, //deprecated query parameter
                $this->post, //post
                $this->getFileUploads($this->files), //files
                $this->cookies, //cookies
                $this->headers, //headers
                $this->method, //method
                $this->remoteAddress, //remoteAddress
                $this->remoteHost, //remoteHost
                $this->rawBodyCallback //rawBodyCallback
            );
        }
        return $this->httpRequest;
    }

    private function getFileUploads($files)
    {
        $out = [];
        foreach ($files as $key=>$file) {
            if (is_array($file)) {
                $out[$key] = $this->getFileUploads($file);
            } elseif (is_string($file)) {
                $out[$key] = $this->createFileUpload($file);
            } elseif ($file instanceof FileUpload) {
                $out[$key] = $file;
            } else {
                throw new InvalidStateException('file uploads expects nested array of strings or FileUploads, '
                    . (is_object($file) ? get_class($file) : gettype($file)) . ' given.');
            }
        }
        return $out;
    }

    public function getHttpResponse()
    {
        if ($this->httpResponse === NULL) {
            $this->httpResponse = new HttpResponse();
        }
        return $this->httpResponse;
    }

    public function getRouter()
    {
        if ($this->router === NULL) {
            $this->router = new SimpleRouter;
        }
        return $this->router;
    }

    public function getRequest()
    {
        if ($this->appRequest === NULL) {
            $this->appRequest = new Request(
                $this->presenterName, //name
                $this->method, //method
                $this->query, //$params
                $this->post, //$post
                $this->files, //files
                $this->appRequestFlags //flags
            );
        }
        return $this->appRequest;
    }

    public function getPresenterFactory()
    {
        if ($this->presenterFactory === NULL) {
            $this->presenterFactory = $this->context->getByType('Nette\Application\IPresenterFactory');
        }
        return $this->presenterFactory;
    }

    /**
     * @param ITemplateFactory $templateFactory
     * @return PresenterTester
     */
    public function setTemplateFactory(ITemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
        return $this;
    }

    /**
     * @param User $user
     * @return PresenterTester
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param Session $session
     * @return PresenterTester
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @param Request $appRequest
     * @return PresenterTester
     */
    public function setAppRequest(Request $appRequest)
    {
        $this->appRequest = $appRequest;
        return $this;
    }

    /**
     * @param IResponse $httpResponse
     * @return PresenterTester
     */
    public function setHttpResponse(IResponse $httpResponse)
    {
        $this->httpResponse = $httpResponse;
        return $this;
    }

    /**
     * @param IRequest $httpRequest
     * @return PresenterTester
     */
    public function setHttpRequest(IRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
        return $this;
    }

    /**
     * @param IRouter $router
     * @return PresenterTester
     */
    public function setRouter(IRouter $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param IPresenterFactory $presenterFactory
     * @return PresenterTester
     */
    public function setPresenterFactory(IPresenterFactory $presenterFactory)
    {
        $this->presenterFactory = $presenterFactory;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $query
     * @return PresenterTester
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param array $query
     * @return PresenterTester
     */
    public function addQuery($query)
    {
        $this->query = $query + $this->query;
        return $this;
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param array $post
     * @return PresenterTester
     */
    public function setPost($post)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @param array
     * @return PresenterTester
     */
    public function addPost($post)
    {
        $this->post = $post + $this->post;
        return $this;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $files
     * @return PresenterTester
     */
    public function setFiles($files)
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @param array $files: values can be one of \Nette\Http\FileUpload, string local file path or nested array
     * @return PresenterTester
     */
    public function addFiles($files)
    {
        $this->files = $files + $this->files;
        return $this;
    }

    /**
     * @return array
     */
    public function getAppRequestFlags()
    {
        return $this->appRequestFlags;
    }

    /**
     * @param array $appRequestFlags
     * @return PresenterTester
     */
    public function setAppRequestFlags($appRequestFlags)
    {
        $this->appRequestFlags = $appRequestFlags;
        return $this;
    }

    /**
     * @param array $appRequestFlags
     * @return PresenterTester
     */
    public function addAppRequestFlags($appRequestFlags)
    {
        $this->appRequestFlags = $appRequestFlags + $this->appRequestFlags;
        return $this;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @param array $cookies
     * @return PresenterTester
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * @param array $cookies
     * @return PresenterTester
     */
    public function addCookies($cookies)
    {
        $this->cookies = $cookies + $this->cookies;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return PresenterTester
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return PresenterTester
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param array $headers
     * @return PresenterTester
     */
    public function addHeaders($headers)
    {
        $this->headers = $headers + $this->headers;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @param string $remoteAddress
     * @return PresenterTester
     */
    public function setRemoteAddress($remoteAddress)
    {
        $this->remoteAddress = $remoteAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteHost()
    {
        return $this->remoteHost;
    }

    /**
     * @param string $remoteHost
     * @return PresenterTester
     */
    public function setRemoteHost($remoteHost)
    {
        $this->remoteHost = $remoteHost;
        return $this;
    }

    /**
     * @return callable|NULL
     */
    public function getRawBodyCallback()
    {
        return $this->rawBodyCallback;
    }

    /**
     * @param callable|NULL $rawBodyCallback
     * @return PresenterTester
     */
    public function setRawBodyCallback(callable $rawBodyCallback = NULL)
    {
        $this->rawBodyCallback = $rawBodyCallback;
        return $this;
    }

    /**
     * @return string
     */
    public function getUploadTempDir()
    {
        return $this->uploadTempDir;
    }
}

class TestResult
{
    private $httpResponse;
    private $responseBody;
    private $appResponse;

    /**
     * TestResult constructor.
     * @param HttpResponse $httpResponse
     * @param string $responseBody
     */
    public function __construct(HttpResponse $httpResponse, $responseBody, IAppResponse $appResponse)
    {
        $this->httpResponse = $httpResponse;
        $this->responseBody = $responseBody;
        $this->appResponse = $appResponse;
    }

    /**
     * @return HttpResponse
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @return IAppResponse
     */
    public function getAppResponse()
    {
        return $this->appResponse;
    }



}
