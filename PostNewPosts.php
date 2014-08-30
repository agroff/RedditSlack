<?php
use \Groff\Command\Option;

class PostNewPosts extends \Groff\Command\Command
{
    protected $description = "Gets new reddit posts and sends them to slack.";

    private $config;
    private $skip;
    private $currentSubreddit;
    private $v;
    private $vv;

    public function setConfig($config){
        $this->config = $config;
    }

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

        foreach ($this->config["subreddits"] as $sub) {
            $this->sendSubredditPosts($sub);
            $this->sleep();
        }


        return 0;
    }

    private function sendSubRedditPosts($sub){

        $this->currentSubreddit = $sub;

        $url = 'http://www.reddit.com/r/'.$sub.'/new.json';

        $json = $this->fetch($url);

        $currentNewPage = json_decode($json, TRUE);

        $sentPosts = $this->loadSentPosts();

        $newPosts = $this->getNewPosts($sentPosts, $currentNewPage);

        $this->sendNewPosts($newPosts);

        $this->recordNewPosts($newPosts, $sentPosts);
    }


    private function fetch($url)
    {

        $this->v("Fetching: " . $url);

        return file_get_contents($url);
    }

    private function loadSentPosts()
    {
        $file = $this->getFilename();

        if(!file_exists($file)){
            return array();
        }

        $json = file_get_contents($file);

        $sentPosts = json_decode($json);

        if(!$sentPosts){
            return array();
        }

        return $sentPosts;
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
        $this->out("Sending Post: " . $post["data"]["title"]);

        $url = $this->config["webhook_url"];


        $body= '<http://reddit.com'.$post["data"]["permalink"].'|'.$post["data"]["title"].'>';

        $fields = array();

        $authorText = $post["data"]["author"];

        if(trim($post["data"]["author_flair_text"]) !== ''){
            $authorText .= ' - '.$post["data"]["author_flair_text"];
        }

        $fields[] = array(
            "title" => "Author",
            "value" => '<http://www.reddit.com/user/'.$post["data"]["author"].'|'.$authorText.'>',
            "short" => true,
        );
        $fields[] = array(
            "title" => "Votes",
            "value" => 'Up: ' . $post["data"]["ups"] . ' | Down: ' . $post["data"]["downs"],
            "short" => true,
        );

        $attachment = array(
            "fallback" => $body,
            "pretext" => $body,
            "color" => "#D00000",
            "fields" => $fields
        );

        $data = array(
            "username" => $this->currentSubreddit,
            "channel" => "#reddit-posts",
            "attachments" => array($attachment),
            "icon_emoji" => ":reddcoin:"
        );

        $data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        //dbg($data_string);

        $result = curl_exec($ch);
        $this->sleep();
    }

    private function recordNewPosts($newPosts, $oldPosts)
    {
        foreach($newPosts as $post){
            //append newly sent posts to the array
            $oldPosts[] = $post["data"]["name"];

            //no need for this to get too big. Remove posts older than 300 posts ago.
            if(count($oldPosts) > 300 ) {
                array_shift($oldPosts);
            }
        }

        $file = $this->getFilename();
        file_put_contents($file, json_encode($oldPosts));
    }


    private function getFilename()
    {
        return $this->currentSubreddit . '.json';
    }

    private function sleep($seconds = 2)
    {
        $this->vv("Sleeping $seconds seconds.");
        sleep($seconds);
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
        $this->addOption(new Option("s", FALSE, "Skips sending new posts, but still fetches and stores them.", "skip"));
    }
}