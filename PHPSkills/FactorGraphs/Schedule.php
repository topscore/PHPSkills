<?php
namespace Moserware\Skills\FactorGraphs;

abstract class Schedule
{
    private $_name;

    protected function __construct($name)
    {
        $this->_name = $name;
    }

    public abstract function visit($depth = -1, $maxDepth = 0);

    public function __toString()
    {
        return $this->_name;
    }
}

class ScheduleStep extends Schedule
{
    private $_factor;
    private $_index;

    public function __construct($name, $factor, $index)
    {
        parent::__construct($name);
        $this->_factor = $factor;
        $this->_index = $index;
    }

    public function visit($depth, $maxDepth)
    {
        $delta = $this->_factor->updateMessageIndex($this->_index);
        return $delta;
    }
}

class ScheduleSequence extends Schedule
{
    private $_schedules;

    public function __construct($name, $schedules)
    {
        parent::__construct($name);
        $this->_schedules = $schedules;
    }

    public function visit($depth, $maxDepth)
    {
        $maxDelta = 0;

        foreach ($this->_schedules as $currentSchedule)
        {
            $maxDelta = max($currentSchedule->visit($depth + 1, $maxDepth), $maxDelta);
        }

        return $maxDelta;
    }
}

class ScheduleLoop extends Schedule
{
    private $_maxDelta;
    private $_scheduleToLoop;

    public function __construct($name, Schedule $scheduleToLoop, $maxDelta)
    {
        parent::__construct($name);
        $this->_scheduleToLoop = $scheduleToLoop;
        $this->_maxDelta = $maxDelta;
    }

    public function visit($depth, $maxDepth)
    {
        $totalIterations = 1;
        $delta = $this->_scheduleToLoop->visit($depth + 1, $maxDepth);
        while ($delta > $this->_maxDelta)
        {
            $delta = $this->_scheduleToLoop->visit($depth + 1, $maxDepth);
            $totalIterations++;
        }

        return $delta;
    }
}

?>