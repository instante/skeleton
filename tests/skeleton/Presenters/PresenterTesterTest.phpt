<?php
namespace Instante\Tests\Presenters;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette;
use Nette\Application\IResponse;
use Nette\Application\UI\ITemplate;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Http\IRequest;
use Nette\Http\IResponse as IHttpResponse;
use Tester\Assert;
use Tester\TestCase;

$context = require __DIR__ . '/../../integration/bootstrap.php';

class PresenterTesterTest extends TestCase
{
    /** @var Container */
    private $context;

    /**
     * SamplePresenterTest constructor.
     * @param Container $context
     */
    public function __construct(Container $context) {
        $this->context = $context;
    }


    public function testConstructor()
    {
        new PresenterTester('Instante\Tests\Presenters\TestPresenter', ':Test', $this->context, '/tmp/some/directory');
        new PresenterTester(new TestPresenter, ':Test', $this->context);
        new PresenterTester(function() { return new TestPresenter; }, ':Test', $this->context);
        Assert::true(TRUE, 'Passing this test indicates that PresenterTester constructor accepts all desired argument types.');
    }

    public function testBasicTest()
    {
        $tester = new PresenterTester(new TestPresenter, ':Test', $this->context);
        $result = $tester->runPresenter();
        Assert::same('Hello world', $result->getResponseBody(), 'Basic presenter execution');
    }

    public function testInjectDependencies()
    {
        $tester = new PresenterTester(new TestInjectPresenter, ':Test', $this->context);
        $tester->injectDependencies([
            'foo'=>new Foo(1),
            'baz'=>($baz = new Foo(4)),
        ]);
        $tester->injectDependencies([
            'foo'=>new Foo(2),
            'bar'=>new Foo(3),
            'foos'=>[new Foo, new Fooo],
        ]);
        $tester->runPresenter();
        $presenter = $baz->presenter;
        Assert::type('Nette\Application\UI\Presenter', $presenter, 'Presenter should have been passed via $baz');
        Assert::same(2, $presenter->foo->no, 'overriden repeatedly injected property');
        Assert::same(3, $presenter->bar->no, 'injected via @inject annotation');
        Assert::same(4, $presenter->baz->no, 'injected single property not wrapped into array via inject*() method');
        Assert::type('Instante\Tests\Presenters\Foo', $presenter->foo2, 'injected two properties via inject*() method');
        Assert::type('Instante\Tests\Presenters\Fooo', $presenter->fooo, 'injected two properties via inject*() method');
    }

    public function testCheckInstanceUsedOnce()
    {
        $tester = new PresenterTester(new TestPresenter, ':Test', $this->context);
        $tester->runPresenter();
        Assert::exception(function() use ($tester) {
            $tester->runPresenter();
        }, 'Nette\InvalidStateException');

        //should not fail - presenter passed by class or factory method can be used multiple times
        $tester = new PresenterTester('Instante\Tests\Presenters\TestPresenter', ':Test', $this->context);
        $tester->runPresenter();
        $tester->runPresenter();
        $tester = new PresenterTester(function() { return new TestPresenter; }, ':Test', $this->context);
        $tester->runPresenter();
        $tester->runPresenter();
    }

    public function testAddFileUpload()
    {
        $tester = new PresenterTester(new TestUploadPresenter, ':Test', $this->context);
        $uploadTest = __DIR__ . '/upload-test';
        $tester->addFileUpload('avatar', $uploadTest);
        $tester->addFailedFileUpload('avatar2');
        $tester->addFileUpload('avatar3', 'foo', $uploadTest);

        Assert::exception(function() use ($tester) {
            $tester->addFileUpload('avatar4', 'this-does-not-exist');
        }, 'Nette\FileNotFoundException');

        $uploads = $tester->runPresenter()->getAppResponse()->object;
        Assert::true(isset($uploads['avatar']));

        $upload = $uploads['avatar'];
        /** @var FileUpload $upload */
        Assert::type('Nette\Http\FileUpload', $upload);
        Assert::same('test1', trim(file_get_contents($upload->getTemporaryFile())));
        Assert::match('~^' . preg_quote(realpath($tester->getUploadTempDir())) . '~', $upload->getTemporaryFile());
        Assert::notSame($upload->getTemporaryFile(), $uploadTest); //was copied to another file

        $upload = $uploads['avatar2'];
        Assert::same(UPLOAD_ERR_NO_FILE, $upload->getError());

        $upload = $uploads['avatar3'];
        Assert::same('foo', $upload->getName());
        Assert::same($uploadTest, $upload->getTemporaryFile());
    }

    public function testUploadNoTempDir()
    {
        $tester = [];
        Assert::error(function() use (&$tester) {
            $tester = new PresenterTester(new TestUploadPresenter, ':Test', new Container);
        }, E_USER_NOTICE);
        Assert::exception(function() use ($tester) {
            $tester->addFileUpload('avatar', __DIR__ . '/upload-test');
        }, 'Nette\InvalidStateException');
    }
}

class Foo
{
    public $no;

    public $presenter;

    public function __construct($no = 0) {
        $this->no = $no;
    }

}

class Fooo
{
}

class TestPresenter extends Presenter
{

    /**
     * @return ITemplate
     */
    protected function createTemplate()
    {
        $engine = new Engine;
        $engine->setLoader(new StringLoader);
        return (new Template($engine))->setFile('Hello world');
    }
}

class TestInjectPresenter extends TestPresenter
{
    /** @var Foo @inject */
    public $foo;

    /** @var Foo @inject */
    public $bar;

    public $baz;

    public $fooo;

    public $foo2;

    public function injectBaz(Foo $baz)
    {
        $this->baz = $baz;
    }

    public function injectFoos(Foo $foo2, Fooo $fooo)
    {
        $this->foo2 = $foo2;
        $this->fooo = $fooo;
    }

    /**
     * @return ITemplate
     */
    protected function createTemplate()
    {
        if ($this->baz) {
            $this->baz->presenter = $this;
        }
        return parent::createTemplate();
    }
}

class ObjectResponse implements IResponse
{
    public $object;

    /**
     * ObjectResponse constructor.
     * @param $object
     */
    public function __construct($object) { $this->object = $object; }


    function send(IRequest $httpRequest, IHttpResponse $httpResponse)
    {

    }
}

class TestUploadPresenter extends TestPresenter
{
    public function actionDefault()
    {
        $files = $this->getRequest()->getFiles();
        $this->sendResponse(new ObjectResponse($files));
        $this->terminate();
    }
}

(new PresenterTesterTest($context))->run();
