<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Exception\EndlessException;
use Jine\EventBus\Exception\NotFoundException;
use Jine\EventBus\Exception\InterfaceMapNotFoundException;
use Jine\EventBus\Exception\DefinitionIsNotObjectTypeException;

use function array_key_exists;
use function is_object;
use function count;
use function current;
use function end;
use function key;
use function prev;
use function array_merge;

class Container
{
    // Массив рефлексий
    protected array $reflections = [];

    // Массив дерева зависимостей
    protected array $dependencies = [];

    // Массив объектов
    protected array $definitions = [];

    // Массив параметров
    protected array $params = [];

    // Сохраненные деревья зависимостей
    protected array $trees = [];

    // Массив сопоставлений Interface => Class
    protected array $classMap = [];

    // Массив поставщиков параметров
    protected array $providers = [];

    protected array $tmp;

    // Флаг синглтона
    protected $singleton = true;

    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }
    
    public static function create(array $providers = []): self
    {
        return new static($providers);
    }

    public function get($className)
    {
        if (!isset($this->definitions[$className])) {
            throw new NotFoundException($className);
        }
        return $this->definitions[$className];
    }

    public function has($className): bool
    {
        return isset($this->definitions[$className]);
    }

    public function set($definition): void
    {
        if (!is_object($definition)) {
            throw new DefinitionIsNotObjectTypeException(gettype($definition));
        }
        $className = $definition::class;

        $this->definitions[$className] = $definition;
    }

    // Создает инстанс переданного класса
    public function instance(string $className, array $params = [], bool $singleton = true)
    {
        $this->tmp = [];

        $this->dependencies = [];

        $this->singleton = $singleton;

        $this->params = $params;

        $this->prepareDependencies($className);

        if (count($this->dependencies) === 1) {
            $this->instanceSingleClass($className);
            return $this->getDefinition($className);
        }

        if (empty($this->dependencies)) {
            $this->instanceClass($className);
            return $this->getDefinition($className);
        }

        $this->iterateDependensies();

        $this->trees[$className] = $this->dependencies;

        return $this->getDefinition($className);
    }

    public function getClassMap(string $className): array
    {
        if (!array_key_exists($className, $this->trees)) {
            $this->instance($className);
        }
        return $this->trees[$className];
    }

    public function setClassMap(array $classMap): void
    {
        $this->classMap = $classMap;
    }

    // Подготавливает зависимости к рекурсивному инстанцированию
    protected function prepareDependencies(string $className): void
    {
        // Проверяем наличие ранее созданного дерева зависимостей для класса
        if (isset($this->trees[$className])) {
            $this->dependencies = $this->trees[$className];
            return;
        }

        // Проверяем наличие ранее созданых рефлексий
        if (!isset($this->reflections[$className])) {
            $this->reflections[$className] = new \ReflectionClass($className);
        }

        // Получаем конструктор
        $constructor = $this->reflections[$className]->getConstructor();

        if ($constructor !== null) {
            $this->buildDependencies($constructor, $className);
        }
    }

    // Рекурсивно выстраивает зависимости
    protected function buildDependencies(\ReflectionMethod $constructor, string $className): void
    {
        // Проходим по параметрам конструктора
        foreach ($constructor->getParameters() as $param) {

            // Получаем класс из подсказки типа
            $class = $param->getClass();

            // Если в параметрах есть зависимость то получаем её
            if (null !== $class) {

                if ($class->isInterface()) {
                    $this->prepareInterface($class, $className);
                    continue;
                }

                $depClassName = $class->getName();

                $this->resolveDependency($className, $depClassName);
            }
        }
    }

    protected function prepareInterface(\ReflectionClass $interface, string $className)
    {
        $depInterfaceName = $interface->getName();

        if (!array_key_exists($depInterfaceName, $this->classMap)) {
            throw new InterfaceMapNotFoundException($depInterfaceName);
        }

        $depClassName = $this->classMap[$depInterfaceName];

        $this->resolveDependency($className, $depClassName);
    }

    protected function resolveDependency(string $className, string $depClassName)
    {
        // Если класс зависит от запрошенного то это циклическая зависимость
        if (isset($this->dependencies[$depClassName][$className])) {
            throw new EndlessException($className, $depClassName);
        }

        $this->dependencies[$className][$depClassName] = $depClassName;
        $this->prepareDependencies($depClassName);
    }

    // Проходит по дереву зависимостей
    protected function iterateDependensies(): void
    {
        $deps = end($this->dependencies);

        while ($deps !== false) {

            $class = key($this->dependencies);

            $deps = current($this->dependencies);

            if (prev($this->dependencies) === false) {
                $this->instanceSingleClass($class, $deps);
                break;
            }

            if (empty($deps)) {
                $this->instanceClass($class);
                continue;
            }

            $this->instanceRecursive($class, $deps);
        }
    }

    // Рекурсивно инстанцирует зависимости
    protected function instanceRecursive(string $class, array $deps = []): void
    {

        $dependencies = [];

        $params = [];

        foreach ($deps as $dep) {

            if ($this->hasDefinition($dep)) {
                $dependencies[] = $this->getDefinition($dep);
                continue;
            } 
    
            if (isset($this->dependencies[$dep])) {

                if ($this->hasDefinition($dep)) {
                    $dependencies[] = $this->getDefinition($dep);
                } elseif ($this->getDefinition($dep) !== null) {

                    if ($this->hasDefinition($dep)) {
                        $this->instanceRecursive($dep, $this->getDefinition($dep));
                    }

                } else {
                    $this->instanceSingleClass($dep);
                }

            } else {

                if ($this->hasDefinition($dep)) {
                    $dependencies[] = $this->getDefinition($dep);
                    continue;
                }

                if (array_key_exists($dep, $this->providers)) {
                    $container = clone($this);
                    $provider = $container->instance($this->providers[$dep]);
                    $params = $provider->getParams();
                    
                    $constructor = $this->reflections[$dep]->getConstructor();

                    if ($constructor !== null) {
                        $this->setDefinition($dep, $this->reflections[$dep]->newInstanceArgs($params));
                    } else {
                        $this->setDefinition($dep, $this->reflections[$dep]->newInstance());
                    }
                    
                    if ($this->hasDefinition($dep)) {
                        $provider->prepare($this->getDefinition($dep));
                    }
                    
                } else {
                    $this->setDefinition($dep, $this->reflections[$dep]->newInstance());
                }

            }

            if (!in_array($this->getDefinition($dep), $dependencies, true)) {
                if ($this->hasDefinition($dep)) {
                    $dependencies[] = $this->getDefinition($dep);
                }
            }
        }

        $this->setDefinition($class, $this->reflections[$class]->newInstanceArgs($dependencies));
    }

    protected function instanceSingleClass(string $class): void
    {
        $params = [];

        if ($this->hasDefinition($class)) {
            return;
        }

        foreach ($this->dependencies[$class] as $dep) {

            if ($this->hasDefinition($dep)) {
                continue;
            }
        
            if (array_key_exists($dep, $this->providers)) {
                $container = clone($this);
                $provider = $container->instance($this->providers[$dep]);
                $params = $provider->getParams();
                
                $constructor = $this->reflections[$dep]->getConstructor();
                
                if ($constructor !== null) {
                    $this->setDefinition($dep, $this->reflections[$dep]->newInstanceArgs($params));
                } else {
                    $this->setDefinition($dep, $this->reflections[$dep]->newInstance());
                }

                if ($this->hasDefinition($dep)) {
                    $provider->prepare($this->getDefinition($dep));
                }
                    
            } else {
                $this->setDefinition($dep, $this->reflections[$dep]->newInstance());
            }

        }

        $this->instanceClass($class, $this->dependencies[$class]);
    }

    protected function instanceClass(string $class, array $deps = []): void
    {
        $dependencies = [];

        foreach ($deps as $dep) {
            if ($this->hasDefinition($dep)) {
                $dependencies[] = $this->getDefinition($dep);
            }
        }

        if (!empty($this->params)) {
            $dependencies = array_merge($dependencies, $this->params);
        }

        if (array_key_exists($class, $this->providers)) {
            $container = clone($this);
            $provider = $container->instance($this->providers[$class]);
            $dependencies = array_merge($dependencies, $provider->getParams());
        }

        $this->setDefinition($class, $this->reflections[$class]->newInstanceArgs($dependencies));
    }

    protected function setDefinition(string $className, $definition): void
    {
        if ($this->singleton && !isset($this->definitions[$className])) {
            $this->definitions[$className] = $definition;
        } else {
            $this->tmp[$className] = $definition;
        }
    }

    protected function getDefinition(string $className)
    {
        if ($this->singleton && $this->hasDefinition($className)) {
            return $this->definitions[$className];
        }
        
        if ($this->hasDefinition($className)) {
            return $this->tmp[$className];
        }
    }

    protected function hasDefinition(string $className): bool
    {
        if ($this->singleton) {
            return isset($this->definitions[$className]);
        }
        return isset($this->tmp[$className]);
    }
}
