<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Action as ApiResourceAction;
use App\Attribute\Action;
use App\Attribute\Setting;
use App\Dto\DtoTransformer\ActionDtoTransformer;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Attribute\Route;

class ActionStateProvider implements ProviderInterface
{
    private array $classRoutes = [];

    public function __construct(
        private readonly ActionDtoTransformer $transformer,
        private readonly ProjectDirService $projectDirService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $actions = [];
        $request = $context['request'];
        $section = $request->query->get('section');
        $type = $request->query->get('type');
        foreach ($this->getActions($section, $type) as $action) {
            $actions[] = $this->transformer->fromAction($action)->toArray();
        }

        return new ArrayCollection($actions);
    }

    public function getActions(?string $section, ?string $type): array
    {
        $filenames = $this->getFilenames($this->projectDirService->path('src', 'Controller'));
        
        $actions = [];
        foreach ($filenames as $filename) {
            $fullNamespace = $this->getFullNamespace($filename);
            if (!$fullNamespace) {
                continue;
            }
            $namespaces = $fullNamespace . '\\' . $this->getClassName($filename);
            $className = $this->getClassName($namespaces);
            $reflectionClass = new ReflectionClass($className);
            foreach ($reflectionClass->getMethods() as $method) {
                $action = $this->getAction($method, $section, $type);
                if ($action) {
                    $action->setClassRoute($this->getClassRoute($className, $reflectionClass));
                    $actions[] = $action;
                }
            }
        }

        return $actions;
    }
    
    private function getRouteName(ReflectionAttribute $attribute): string
    {
        $arguments = $attribute->getArguments();

        return (array_key_exists('name', $arguments)) ? $arguments['name'] : '';
    }

    private function getAction(ReflectionMethod $method, ?string $section, ?string $type): ?ApiResourceAction
    {
        $methodRouteName = '';
        $action = null;
        foreach ($method->getAttributes() as $attribute) {
            $objectAttribut = $attribute->newInstance();
            if ($objectAttribut instanceof Route) {
                $methodRouteName = $this->getRouteName($attribute);
            }
            if ($objectAttribut instanceof Setting || $objectAttribut instanceof Action) {
                $attribubeType = strtolower((new ReflectionClass($objectAttribut))->getShortName());
                if ($section && $section !== $objectAttribut->getSection()) {
                    continue;
                }
                if ($type && $type !== $attribubeType) {
                    continue;
                }
                $action = new ApiResourceAction();
                $action->setType($attribubeType)
                    ->setMethodRoute($methodRouteName)
                    ->setSection($objectAttribut->getSection())
                    ->setIcon($objectAttribut->getIcon());
            }
        }

        return $action;
    }


    private function getClassRoute(string $className, ReflectionClass $reflectionClass): string
    {
        if (!array_key_exists($className, $this->classRoutes)) {
            foreach ($reflectionClass->getAttributes() as $attribute) {
                $objectAttribut = $attribute->newInstance();
                if ($objectAttribut instanceof Route) {
                    $this->classRoutes[$className] = $this->getRouteName($attribute);
                }
            }
        }
       
        return $this->classRoutes[$className];
    }

    private function getClassName($filename)
    {
        $directoriesAndFilename = explode('/', $filename);
        $filename = array_pop($directoriesAndFilename);
        $nameAndExtension = explode('.', $filename);
        $className = array_shift($nameAndExtension);
        return $className;
    }

    private function getFullNamespace($filename)
    {
        $lines = file($filename);
        $array = preg_grep('/^namespace /', $lines);
        if (empty($array)) {
            return null;
        }
        $namespaceLine = array_shift($array);
        $match = [];
        preg_match('/^namespace (.*);$/', $namespaceLine, $match);
        $fullNamespace = array_pop($match);

        return $fullNamespace;
    }

    private function getFilenames($path)
    {
        $finderFiles = Finder::create()->files()->in($path)->name('*.php');
        $filenames = [];
        foreach ($finderFiles as $finderFile) {
            $filenames[] = $finderFile->getRealpath();
        }
        return $filenames;
    }
}
