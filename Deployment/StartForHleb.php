<?php

declare(strict_types=1);

namespace Phphleb\Combinator\Deployment;

use Hleb\Helpers\ArrayHelper;
use Hleb\Main\Console\Commands\Deployer\LibDeployerFinder;
use Hleb\Static\Settings;
use Hleb\Main\Console\Commands\Deployer\DeploymentLibInterface;
use Hleb\Main\Console\Commands\Deployer\LibDeployerCreator;

class StartForHleb implements DeploymentLibInterface
{
    private const EXCLUDED_LIBS = ['phphleb/combinator'];

    private bool $noInteraction = false;

    private bool $quiet = false;

    /**
     * @param array $config - configuration for deploying libraries,
     *                        sample in updater.json file.
     *                      - конфигурация для развертывания библиотек,
     *                        образец в файле updater.json.
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function noInteraction(): void
    {
        $this->noInteraction = true;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function quiet(): void
    {
        $this->quiet = true;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function help(): string|false
    {
        return 'Installing or removing library components in a project based on the HLEB framework.';
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function add(): int
    {
        $code = 0;
        /**
         * @var DeploymentLibInterface $component
         */
        foreach($this->searchAllComponents() as $component) {
            if ($this->noInteraction) {
                $component->noInteraction();
            }
            if ($component->add() !== 0) {
                $code = 1;
            }
        }
        return $code;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function remove(): int
    {
        $code = 0;
        /**
         * @var DeploymentLibInterface $component
         */
        foreach($this->searchAllComponents() as $component) {
            if ($this->noInteraction) {
                $component->noInteraction();
            }
            if ($this->quiet) {
                $component->quiet();
            }
            if ($component->remove() !== 0) {
                $code = 1;
            }
        }
        return $code;
    }

    /**
     * Returns the library class loading map required for deployment
     * in the format: namespace => realpath.
     * This is needed in case for some reason these classes
     * are not supported by the current class loaders.
     * In most cases, returning an empty array will suffice.
     * 
     * Возвращает необходимую для развертывания карту загрузки
     * классов библиотеки в формате: namespace => realpath.
     * Это нужно в случае, если по какой-то причине эти классы
     * не поддерживаются текущими загрузчиками классов.
     * В большинстве случаев будет достаточно вернуть пустой массив.
     *
     * @inheritDoc
     */
    #[\Override]
    public function classmap(): array
    {
        $result = [];
        /**
         * @var DeploymentLibInterface $component
         */
        foreach ($this->searchAllComponents() as $component) {
            $result = \array_merge($component->classmap(), $result);
        }
        return $result;
    }

    /**
     * Collection of all classes of `StartForHleb` libraries from the vendor folder.
     * Valid libraries of the form must be specified in the configuration:
     * ['components' => ['author/library-name']]
     * The `StartForHleb` class must be located in the Deployment folder of the library and have
     * namespace like `Author\LibraryName\Deployment\` and implemented
     * interface `Phphleb\Combinator\Deployment\DeploymentLibInterface`.
     * If these conditions are met, this script will load the required library.
     * 
     * Сбор всех классов `StartForHleb` библиотек из папки vendor.
     * В конфигурации должны быть заданы допустимые библиотеки вида:
     * ['components' => ['author/library-name']]
     * Класс `StartForHleb` должен находиться в папке Deployment библиотеки и иметь
     * namespace вида `Author\LibraryName\Deployment\` и имплементировать
     * интерфейс `Phphleb\Combinator\Deployment\DeploymentLibInterface`.
     * При соблюдении этих условий этот скрипт загрузит необходимую библиотеку.
     */
    private function searchAllComponents(): array
    {
        $result = [];
        $path = Settings::getPath('vendor');
        $choice = array_keys($this->config['settings']); // as ['author/library-name']
        $finder = new LibDeployerFinder();
        $deployer = new LibDeployerCreator();
        foreach ($choice as $lib) {
            if (!\in_array($lib, self::EXCLUDED_LIBS) && $finder->isExists($lib)) {
                $file = $path . '/' . $lib . '/Deployment/StartForHleb.php';
                $libConfigFile = $path . '/' . $lib . '/updater.json';
                if (\file_exists($file) && \file_exists($libConfigFile)) {
                    $libConfig = \json_decode(\file_get_contents($libConfigFile), true);
                    $config = ArrayHelper::append($libConfig, $this->config['settings'][$lib]);
                    $result[$lib] = $deployer->createDeployer($lib, $config);
                }
            }
        }
        return $result;
    }
}