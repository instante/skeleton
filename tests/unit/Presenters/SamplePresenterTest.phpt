<?php
namespace Instante\Tests\Presenters;


use App\Presenters\HomepagePresenter;
use Instante\RequireJS\Components\JsLoaderFactory;
use Instante\RequireJS\JsModuleContainer;
use Instante\Tests\TestBootstrap;
use Nette\Application\BadRequestException;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$tester = new PresenterTester(HomepagePresenter::class, TestBootstrap::$tempDir);
$jsmc = new JsModuleContainer(__DIR__ . '/../../../frontend/RequireJSDependencies.json');
$tester->getDependencyContainer()->addDependencies([
    'jsModuleContainer' => $jsmc,
    'jsLoaderFactory' => new JsLoaderFactory(false, false, [], $jsmc),
]);
$result = $tester->runPresenter();
Assert::match('~^{block content}~', $result->getResponseBody());

$tester->getRequestBuilder()->addQuery(['action' => 'nonExistentPage']);
Assert::exception(function () use ($tester) {
    $tester->runPresenter();
}, BadRequestException::class);

