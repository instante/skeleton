<?php
namespace Instante\Tests\Presenters;


use App\Presenters\HomepagePresenter;
use Instante\Tests\TestBootstrap;
use Nette\Application\BadRequestException;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$tester = new PresenterTester(HomepagePresenter::class, TestBootstrap::$tempDir);
$result = $tester->runPresenter();
Assert::match('~^{block content}~', $result->getResponseBody());

$tester = new PresenterTester(HomepagePresenter::class, TestBootstrap::$tempDir);
$tester->getRequestBuilder()->addQuery(['action' => 'nonExistentPage']);
Assert::exception(function () use ($tester) {
    $tester->runPresenter();
}, BadRequestException::class);

