<?php
use \Groff\Command\Option;

class PostNewPosts extends \Groff\Command\Command
{
    protected $description = "Gets new reddit posts and sends them to slack.";

    private $skip;
    private $v;
    private $vv;

    /**
     * Contains the main body of the command
     *
     * @return Int Status code - 0 for success
     */
    public function main()
    {
        $this->skip = $this->option("skip");
        $this->v    = $this->option("v");
        $this->vv   = $this->option("vv");

        $url = 'http://www.reddit.com/r/reddCoin/new.json';

        $json = $this->fetch($url);

        $currentNewPage = json_decode($json, TRUE);

        $sentPosts = $this->loadSentPosts();

        $newPosts = $this->getNewPosts($sentPosts, $currentNewPage);

        $this->sendNewPosts($newPosts);

        $this->recordNewPosts($newPosts);

        return 0;
    }


    private function fetch($url)
    {

        $this->v("Fetching: " . $url);

        return file_get_contents($url);
    }

    private function loadSentPosts()
    {
        return array("t3_2eufc9", "t3_2eum30");

        return array();
    }

    private function getNewPosts($sentPosts, $currentNewPage)
    {
        $posts = $currentNewPage["data"]["children"];

        $newPosts = array();

        foreach ($posts as $post) {
            $id    = $post["data"]["name"];
            $title = $post["data"]["title"];

            $this->vv("Post ID: " . $id);
            $this->v("Checking Title: " . $title);

            if (!in_array($id, $sentPosts)) {
                $newPosts[] = $post;
                $this->v("New post found.");
            }
        }

        return $newPosts;
    }

    private function sendNewPosts($newPosts)
    {
        if ($this->skip === TRUE) {
            return;
        }

        foreach ($newPosts as $post) {
            $this->sendPost($post);
        }
    }

    private function sendPost($post)
    {
        $this->v("Pretending to send...");
    }

    private function recordNewPosts($newPosts)
    {

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
        $this->addOption(new Option("v", TRUE, "Verbose output.", "verbose"));
        $this->addOption(new Option("vv", FALSE, "Very Verbose output.", "veryVerbose"));
        $this->addOption(new Option("s", FALSE, "Skips sending new posts, but still fetches and stores them.", "skip"));
    }
}