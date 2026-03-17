<?php
namespace SearchTracker\Rus\Hooks\Handler;

/**
 * Trait ActionHandler
 *
 * Provides methods to handle actions and filters.
 */
trait ActionHandler
{
    /**
     * @var array An array of registered actions.
     */
    protected $actions = [];

    /**
     * @var array An array of registered filters.
     */
    protected $filters = [];

    /**
     * Register an action.
     *
     * @param string   $hook          The name of the WordPress action to which the $callback function is hooked.
     * @param string   $component     A reference to the instance of the class on which the action is defined.
     * @param callable $callback      The name of the function you wish to be called.
     * @param int      $priority      Optional. Used to specify the order in which the functions associated with a particular action are executed.
     * @param int      $accepted_args Optional. The number of arguments the function accepts.
     */
    public function addAction( $hook, $component, $callback, $priority = 10, $accepted_args = 1 )
    {
        $this->actions[] = [
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Register a filter.
     *
     * @param string   $hook          The name of the WordPress filter to which the $callback function is hooked.
     * @param string   $component     A reference to the instance of the class on which the filter is defined.
     * @param callable $callback      The name of the function you wish to be called.
     * @param int      $priority      Optional. Used to specify the order in which the functions associated with a particular filter are executed.
     * @param int      $accepted_args Optional. The number of arguments the function accepts.
     */
    public function addFilter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 )
    {
        $this->filters[] = [
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Run registered actions and filters.
     */
    public function run()
    {
        foreach ($this->filters as $hook) {
            add_filter( $hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['accepted_args'] );
        }

        foreach ($this->actions as $hook) {
            add_action( $hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['accepted_args'] );
        }
    }
}