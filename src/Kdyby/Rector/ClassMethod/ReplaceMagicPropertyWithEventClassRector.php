<?php

declare(strict_types=1);

namespace Rector\Nette\Kdyby\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\Kdyby\DataProvider\EventAndListenerTreeProvider;
use Rector\Nette\Kdyby\Naming\EventClassNaming;
use Rector\Nette\Kdyby\NodeAnalyzer\GetSubscribedEventsClassMethodAnalyzer;
use Rector\Nette\Kdyby\NodeManipulator\ListeningClassMethodArgumentManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Nette\Tests\Kdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\ReplaceMagicPropertyWithEventClassRectorTest
 */
final class ReplaceMagicPropertyWithEventClassRector extends AbstractRector
{
    public function __construct(
        private EventAndListenerTreeProvider $eventAndListenerTreeProvider,
        private EventClassNaming $eventClassNaming,
        private ListeningClassMethodArgumentManipulator $listeningClassMethodArgumentManipulator,
        private GetSubscribedEventsClassMethodAnalyzer $getSubscribedEventsClassMethodAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change getSubscribedEvents() from on magic property, to Event class',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Kdyby\Events\Subscriber;

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            AlbumService::class . '::onApprove' => 'onAlbumApprove',
        ];
    }

    public function onAlbumApprove(Album $album, int $adminId): void
    {
        $album->play();
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use Kdyby\Events\Subscriber;

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            AlbumServiceApproveEvent::class => 'onAlbumApprove',
        ];
    }

    public function onAlbumApprove(AlbumServiceApproveEventAlbum $albumServiceApproveEventAlbum): void
    {
        $album = $albumServiceApproveEventAlbum->getAlbum();
        $album->play();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->getSubscribedEventsClassMethodAnalyzer->detect($node)) {
            return null;
        }

        $this->replaceEventPropertyReferenceWithEventClassReference($node);

        $eventAndListenerTrees = $this->eventAndListenerTreeProvider->provide();
        if ($eventAndListenerTrees === []) {
            return null;
        }

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($eventAndListenerTrees as $eventAndListenerTree) {
            $this->listeningClassMethodArgumentManipulator->changeFromEventAndListenerTreeAndCurrentClassName(
                $eventAndListenerTree,
                $className
            );
        }

        return $node;
    }

    private function replaceEventPropertyReferenceWithEventClassReference(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            $arrayKey = $node->key;
            if (! $arrayKey instanceof Expr) {
                return null;
            }

            $eventPropertyReferenceName = $this->valueResolver->getValue($arrayKey);

            // is property?
            if (! Strings::contains($eventPropertyReferenceName, '::')) {
                return null;
            }

            $eventClassName = $this->eventClassNaming->createEventClassNameFromClassPropertyReference(
                $eventPropertyReferenceName
            );

            $node->key = $this->nodeFactory->createClassConstReference($eventClassName);
            return $node;
        });
    }
}
