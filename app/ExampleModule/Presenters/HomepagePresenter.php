<?php
namespace App\ExampleModule\Presenters;

use App\Presenters\BasePresenter;
use Instante\Bootstrap3Renderer\BootstrapRenderer;
use Nette\Application\UI\Form;

class HomepagePresenter extends BasePresenter
{

    public function createComponentDemoForm()
    {
        $form = new Form;
        if (class_exists('Instante\Bootstrap3Renderer\BootstrapRenderer')) {
            $form->setRenderer(new BootstrapRenderer);
        }

        $form->addGroup('Text fields');
        $form->addText('email', 'Email')
            ->setType('email')
            ->setOption('placeholder', 'Email');
        $form->addTextArea('textarea', 'Textarea');

        $form->addPassword('password', 'Heslo')
            ->setOption('placeholder', 'Heslo');

        $form->addGroup('Other fields');
        $form->addSelect('select', 'Select', ['foo' => 'Foo', 'bar' => 'Bar'])
            ->setPrompt('-choose-')
            ->addError('This field is in an error state');
        $form->addCheckboxList('checkboxlist', 'Checkbox List', ['foo' => 'Foo', 'bar' => 'Bar']);
        $form->addRadioList('rlist', 'Radio List', ['foo' => 'Foo', 'bar' => 'Bar']);
        $form->addCheckbox('remember', 'Neodhlašovat');

        $form->addSubmit('send', 'Přihlásit')
            ->setOption('btn-class', 'btn-primary');
        $form->addSubmit('s2', 'Submit 2');

        $form->addError('This is global form error');
        return $form;
    }
}
