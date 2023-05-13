<?php

declare(strict_types=1);

namespace Warp\DevTool\Monorepo;

use Warp\DevTool\Monorepo\Composer\ComposerJson;
use Warp\DevTool\Monorepo\Composer\MonorepoConfig;

final class ComposerPackagesCollation
{
    /**
     * @var array<string,string>
     */
    private array $autoloadDirs;

    /**
     * @var array<string,string>
     */
    private array $autoloadFiles;

    /**
     * @var array<string,string>
     */
    private array $autoloadDevDirs;

    /**
     * @var array<string,string>
     */
    private array $autoloadDevFiles;

    public function __construct(
        private readonly ComposerJson $composer,
    ) {
        $monorepo = MonorepoConfig::fromComposer($this->composer);

        [$this->autoloadDirs, $this->autoloadFiles] = self::collate($this->composer, $monorepo);
        [$this->autoloadDevDirs, $this->autoloadDevFiles] = self::collate(
            $this->composer,
            $monorepo,
            ComposerJson::AUTOLOAD_DEV
        );
    }

    public function getPackageName(string $file): ?string
    {
        $package = $this->autoloadFiles[$file] ?? $this->autoloadDevFiles[$file] ?? null;

        if (null !== $package) {
            return $package;
        }

        foreach ($this->autoloadDirs as $dir => $package) {
            if (\str_starts_with($file, $dir)) {
                return $package;
            }
        }

        foreach ($this->autoloadDevDirs as $dir => $package) {
            if (\str_starts_with($file, $dir)) {
                return $package;
            }
        }

        return null;
    }

    public function getPackageVersion(string $package): string
    {
        $require = $this->composer->getSection(ComposerJson::REQUIRE);
        $requireDev = $this->composer->getSection(ComposerJson::REQUIRE_DEV);

        return $require[$package] ?? $requireDev[$package] ?? '*';
    }

    /**
     * @return array{array<string,string>,array<string,string>}
     * @throws \JsonException
     */
    private static function collate(
        ComposerJson $composer,
        MonorepoConfig $monorepo,
        string $section = ComposerJson::AUTOLOAD
    ): array {
        $autoloadDirs = [];
        $autoloadFiles = [];

        $monorepoDir = \dirname($composer->getFilename()) . '/';

        foreach ($monorepo->getProjects() as $project) {
            $projectComposer = ComposerJson::read($monorepoDir . $project->getDir() . '/composer.json');
            $projectAutoload = $projectComposer->getSection($section, []);

            foreach ($projectAutoload['psr-4'] ?? [] as $dirs) {
                foreach ((array)$dirs as $dir) {
                    $autoloadDirs[$monorepoDir . $project->getDir() . '/' . $dir] = $projectComposer->getName();
                }
            }
            foreach ($projectAutoload['classmap'] ?? [] as $dir) {
                $autoloadDirs[$monorepoDir . $project->getDir() . '/' . $dir] = $projectComposer->getName();
            }
            foreach ($projectAutoload['files'] ?? [] as $files) {
                foreach ((array)$files as $file) {
                    $autoloadFiles[$monorepoDir . $project->getDir() . '/' . $file] = $projectComposer->getName();
                }
            }
        }

        $vendorDir = $monorepoDir . 'vendor/';
        $lockFilename = $monorepoDir . 'composer.lock';
        $lockFileContent = \file_get_contents($lockFilename);
        \assert(\is_string($lockFileContent));

        $lock = \json_decode($lockFileContent, true, 512, \JSON_THROW_ON_ERROR);

        foreach (['packages', 'packages-dev'] as $key) {
            foreach ($lock[$key] as $package) {
                foreach ($package[$section]['psr-4'] ?? [] as $dirs) {
                    foreach ((array)$dirs as $dir) {
                        $autoloadDirs[$vendorDir . $package['name'] . '/' . $dir] = $package['name'];
                    }
                }
                foreach ($package[$section]['classmap'] ?? [] as $dir) {
                    $autoloadDirs[$vendorDir . $package['name'] . '/' . $dir] = $package['name'];
                }
                foreach ($package[$section]['files'] ?? [] as $files) {
                    foreach ((array)$files as $file) {
                        $autoloadFiles[$vendorDir . $package['name'] . '/' . $file] = $package['name'];
                    }
                }
            }
        }

        return [$autoloadDirs, $autoloadFiles];
    }
}
