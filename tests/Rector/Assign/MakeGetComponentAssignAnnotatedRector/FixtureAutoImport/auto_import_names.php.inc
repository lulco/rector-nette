<?php

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\FixtureAutoImport;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        $anotherControl = $externalControl->getComponent('another');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\FixtureAutoImport;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;
use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var AnotherControl $anotherControl */
        $anotherControl = $externalControl->getComponent('another');
    }
}

?>
