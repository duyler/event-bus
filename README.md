![build](https://github.com/jine-framework/event-bus/workflows/build/badge.svg)
# Event Bus

## Base usage
```
$bus = Jine\EventBus\Bus::create();

$actionStart = new Action('Start', 'App\Handlers\First\Handler');
$actionStart->serviceId('First');

$bus->addAction($actionStart);

$actionShow = new Action('Show', 'App\Handlers\Second\Handler');
$actionShow->serviceId('Second')
             ->required(['First.Start', 'Third.Description']);

$bus->addAction($actionShow);

$actionDescription = new Action('Description', 'App\Handlers\Third\Handler');
$actionDescription->serviceId('Third');

$bus->addAction($actionDescription);

$bus->subscribe('First.Start.Success', 'Second.Show');

$bus->run('First.Start');
