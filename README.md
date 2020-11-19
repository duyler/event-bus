# Event Bus

## Use
```
$bus = Jine\EventBus\Bus::create();

$bus->setCachePath('path to cache dir');

$bus->registerService('First')
    ->action('Start')
    ->handler('App\Handlers\First\Handler');

$bus->registerService('Second')
    ->action('Show')
    ->handler('App\Handlers\Second\Handler')
    ->required(['First.Start', 'Third.Description']);

$bus->registerService('Third')
    ->action('Description')
    ->handler('App\Handlers\Third\Handler');

$bus->subscribe('First.Start.Success', 'Second.Show');

$bus->run('First.Start');
