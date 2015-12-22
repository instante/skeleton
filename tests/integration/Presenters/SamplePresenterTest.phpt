<?php
namespace Instante\Tests\Presenters;


use Nette\Application\BadRequestException;
use Tester\Assert;

$context = require __DIR__ . '/../bootstrap.php';

$tester = new PresenterTester('App\Presenters\HomepagePresenter', ':Homepage', $context);
$result = $tester->runPresenter();
Assert::match('~^<!DOCTYPE~', $result->getResponseBody());

$tester = new PresenterTester('App\Presenters\HomepagePresenter', ':Homepage', $context);
$tester->addQuery(['action' => 'nonExistentPage']);
Assert::exception(function () use ($tester) {
    $tester->runPresenter();
}, BadRequestException::class);

