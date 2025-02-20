<?php

declare(strict_types=1);

namespace Rector\Nette\FileProcessor;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Nette\Contract\Rector\NeonRectorInterface;

final class NeonFileProcessor implements FileProcessorInterface
{
    /**
     * @param NeonRectorInterface[] $neonRectors
     */
    public function __construct(
        private array $neonRectors
    ) {
    }

    /**
     * @param File[] $files
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            $fileContent = $file->getFileContent();

            foreach ($this->neonRectors as $neonRector) {
                $fileContent = $neonRector->changeContent($fileContent);
            }

            $file->changeFileContent($fileContent);
        }
    }

    public function supports(File $file): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['neon'];
    }
}
