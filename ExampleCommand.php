<?php
use \Groff\Command\Option;

class ExampleCommand extends \Groff\Command\Command
{
    protected $description = "Says hi to the user.";

    private $userName;
    private $v;
    private $vv;

    /**
     * Contains the main body of the command
     *
     * @return Int Status code - 0 for success
     */
    function main()
    {
        $this->userName = $this->option("name");
        $this->v        = $this->option("v");
        $this->vv       = $this->option("vv");

        $this->vv("Starting Command...");
        $this->out("Hello " . $this->userName);
        $this->v("Finished.");

        return 0;
    }

    private function out($string)
    {
        if (is_array($string)) {
            print_r($string);

            return;
        }

        echo $string . "\n";
    }

    private function v($string)
    {
        if ($this->v === FALSE && $this->vv === FALSE) {
            return;
        }
        $this->out($string);
    }

    private function vv($string)
    {
        if ($this->vv === FALSE) {
            return;
        }
        $this->out($string);
    }

    protected function printUsage($scriptName)
    {
        echo "Usage: $scriptName \n";
    }

    protected function addOptions()
    {
        $this->addOption(new Option("v", FALSE, "Verbose output.", "verbose"));
        $this->addOption(new Option("vv", FALSE, "Very Verbose output.", "veryVerbose"));
        $this->addOption(new Option("n", 'World', "Set the users name.", "name"));
    }
}