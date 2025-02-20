<?php

namespace Rector\Nette\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class InOnSuccessCall
{
    public function run()
    {
        $form = $this->makeForm();

        $onSuccess = function (Form  $form) {
            $form['email_else']->value = 'hey@hi.hello';
        };
    }

    public function makeForm(): Form
    {
        $form = new Form();
        $form->addText('email_else', 'Email');

        return $form;
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class InOnSuccessCall
{
    public function run()
    {
        $form = $this->makeForm();

        $onSuccess = function (Form  $form) {
            /** @var \Nette\Forms\Controls\TextInput $emailElseControl */
            $emailElseControl = $form['email_else'];
            $emailElseControl->value = 'hey@hi.hello';
        };
    }

    public function makeForm(): Form
    {
        $form = new Form();
        $form->addText('email_else', 'Email');

        return $form;
    }
}

?>
