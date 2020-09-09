<?php

namespace ShopmacherImageServer5\Services;

use ShopmacherImageServer5\Utils\Config;
use Shopware\Bundle\MediaBundle\MediaMigration as ShopwareMediaMigration;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class MediaMigration extends ShopwareMediaMigration
{
    /**
     * @var ShopwareMediaMigration
     */
    private $mediaMigration;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(ShopwareMediaMigration $mediaMigration, ModelManager $modelManager, Config $config)
    {
        $this->mediaMigration = $mediaMigration;
        $this->modelManager = $modelManager;
        $this->config = $config;
    }

    /**
     * @var array
     */
    private $counter = [
        'migrated' => 0,
        'skipped' => 0,
        'moved' => 0,
    ];

    /**
     * Batch migration
     *
     * @param bool $skipScan
     */
    public function migrate(MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFileSystem, OutputInterface $output, $skipScan = false)
    {
        // run default if migration is not from "local" to "imageserver"
        if ($fromFilesystem->getAdapterType() !== 'local' || $toFileSystem->getAdapterType() !== 'imageserver') {
            return $this->mediaMigration->migrate($fromFilesystem, $toFileSystem, $output, $skipScan);
        }

        // Otherwise run copied version of media migration which will skip migrating thumbnails.
        // We don't need thumbnails on ImageServer and we can not in other way prevent the MigrationServer from migrating them.

        $output->writeln(' // Migrating all media files in your filesystem. This might take some time, depending on the number of media files you have.');
        $output->writeln('');

        $filesToMigrate = 0;

        if (!$skipScan) {
            $filesToMigrate = $this->countFilesToMigrate('media', $fromFilesystem);
        }

        $progressBar = new ProgressBar($output, $filesToMigrate);
        $progressBar->setFormat(" %current%/%max% [%bar%] %percent%%,  %migrated% migrated, %skipped% skipped, %moved% moved, Elapsed: %elapsed%\n Current file: %filename%");
        $progressBar->setMessage('', 'filename');

        try {
            $this->migrateFilesIn('media', $fromFilesystem, $toFileSystem, $progressBar);
        } catch (\Exception $exception) {
            $output->writeln('Error: ' . $exception->getMessage());
        }

        $progressBar->finish();

        $rows = [];
        foreach ($this->counter as $key => $value) {
            $rows[] = [$key, $value];
        }

        $output->writeln('');
        $output->writeln('');

        $table = new Table($output);
        $table->setStyle('borderless');
        $table->setHeaders(['Action', 'Number of items']);
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Migrate a single file
     *
     * @param string $path
     *
     * @throws \RuntimeException
     */
    private function migrateFile($path, MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFileSystem)
    {
        // Skip migration for path if path is a thumbnail and don't have a Media entity
        $normalized = $fromFilesystem->normalize($path);
        $media = $this->modelManager->getRepository(Media::class)->findOneBy(['path' => $normalized]);
        if (!$media) {
            ++$this->counter['skipped'];
            return;
        }

        // only do migration if it's on the local filesystem since could take a long time
        // to read and write all the files
        if ($fromFilesystem->getAdapterType() === 'local') {
            if (!$fromFilesystem->isEncoded($path)) {
                ++$this->counter['migrated'];
                $fromFilesystem->migrateFile($path);
            }
        }

        // file already exists
        if ($toFileSystem->has($path)) {
            ++$this->counter['skipped'];

            return;
        }

        // move file to new filesystem and remove the old one
        if ($fromFilesystem->has($path)) {
            ++$this->counter['moved'];
            $success = $this->writeStream($toFileSystem, $path, $fromFilesystem->readStream($path));

            // check if file needs to be deleted
            if ($success && $this->config->deleteAfterMigration()) {
                $fromFilesystem->delete($path);
            }

            return;
        }

        throw new \RuntimeException('File not found: ' . $path);
    }

    /**
     * @param string   $path
     * @param resource $contents
     *
     * @return bool
     */
    private function writeStream(MediaServiceInterface $toFileSystem, $path, $contents)
    {
        $path = $toFileSystem->encode($path);

        $dirString = '';
        $dirs = explode('/', dirname($path));
        foreach ($dirs as $dir) {
            $dirString .= '/' . $dir;
            $toFileSystem->createDir($dirString);
        }

        $toFileSystem->writeStream($path, $contents);

        return $toFileSystem->has($path);
    }

    /**
     * @param string $directory
     */
    private function migrateFilesIn($directory, MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFilesystem, ProgressBar $progressBar)
    {
        /** @var array $contents */
        $contents = $fromFilesystem->getFilesystem()->listContents($directory);

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $this->migrateFilesIn($item['path'], $fromFilesystem, $toFilesystem, $progressBar);
                continue;
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                $progressBar->setMessage($item['path'], 'filename');

                try {
                    $this->migrateFile($item['path'], $fromFilesystem, $toFilesystem);
                } catch (\Exception $exception) {
                    echo "\n" . $exception->getMessage() . "\n";
                }

                foreach ($this->counter as $key => $value) {
                    $progressBar->setMessage($value, $key);
                }

                $progressBar->advance();
            }
        }
    }

    /**
     * @param string $directory
     *
     * @return int
     */
    private function countFilesToMigrate($directory, MediaServiceInterface $filesystem)
    {
        /** @var array $contents */
        $contents = $filesystem->getFilesystem()->listContents($directory);
        $cnt = 0;

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $cnt += $this->countFilesToMigrate($item['path'], $filesystem);
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                ++$cnt;
            }
        }

        return $cnt;
    }
}
