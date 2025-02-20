<?php

namespace Rector\Nette\Tests\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture;

use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatchEvent(static::class . '::onCopy', new EventArgsList([$this, $key]));
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture;

use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatch(new \Rector\Nette\Tests\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture\Event\SomeClassCopyEvent($this, $key));
    }
}

?>
