<?php
namespace Instante\Tests\Presenters;


use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$context = require __DIR__ . '/../bootstrap.php';

class SamplePresenterTest extends TestCase
{
    /** @var Container */
    private $context;

    /**
     * SamplePresenterTest constructor.
     * @param Container $context
     */
    public function __construct(Container $context)
    {
        $this->context = $context;
    }

    public function testHomepage()
    {
        $tester = new PresenterTester('App\Presenters\HomepagePresenter', ':Homepage', $this->context);
        $result = $tester->runPresenter();

        Assert::match('~^<!DOCTYPE~', $result->getResponseBody());

    }
}

(new SamplePresenterTest($context))->run();
