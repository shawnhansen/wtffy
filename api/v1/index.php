<?php

    require 'Slim/Slim.php';
    \Slim\Slim::registerAutoloader();

    $app = new \Slim\Slim();

    $app->get('/events', 'getEvents');
    $app->get('/news/:category', 'getNews');
    $app->get('/twitter/:user', 'getTwitter');
    $app->get('/facebook/:user', 'getFacebook');
    $app->get('/youtube/:user', 'getYoutube');
    $app->get('/flickr/:user', 'getFlickr');

    $app->run();



    function getEvents() {
        // Doesn't do anything yet
        
        //let the browsers know the output is JSON
        header('content-type: application/json; charset=utf-8');
        //allow cross-site access
        header("access-control-allow-origin: *");
    }


    function getNews($category) {
        // Intended to pull JSON of a Wordpress category
        // Must have http://wordpress.org/plugins/json-api/ installed on WP site
        
        //let the browsers know the output is JSON
        header('content-type: application/json; charset=utf-8');
        //allow cross-site access
        header("access-control-allow-origin: *");

        $file = "cache/news/".$category.".json";

        if (file_exists($file) && (time()-filemtime($file) < 15 * 60)) {
            $news = json_decode(file_get_contents($file));
            echo json_encode($news);
        } else {
            $request_url = "http://{YOUR_WORDPRESS_URL}/api/get_category_posts/?category_slug=".$category;
            $json = file_get_contents($request_url, true);
            $news = json_decode($json, true);

            $fh = fopen($file, 'w') or die("can't open file");
            fwrite($fh, json_encode($news));
            fclose($fh);

            $news = json_decode(file_get_contents($file));
            echo json_encode($news);
        }
    }

    function getTwitter($user) {
        // Pulls a JSON feed of $user tweets. Requires authentication and twitteroauth library

        //let the browsers know the output is JSON
        header('content-type: application/json; charset=utf-8');
        //allow cross-site access
        header("access-control-allow-origin: *");

        $twitteruser = $user;
        $file = "cache/twitter/".$twitteruser."-tweets.json";

        if (file_exists($file) && (time()-filemtime($file) < 15 * 60)) {
            $tweets = json_decode(file_get_contents($file));
            echo json_encode($tweets);
        } else {
            session_start();
            require_once('twitteroauth/twitteroauth/twitteroauth.php');

            $notweets = 30;
            $consumerkey = "{YOUR_KEY}";
            $consumersecret = "{YOUR_SECRET}";
            $accesstoken = "{YOUR_TOKEN}";
            $accesstokensecret = "{YOUR_TOKEN_SECRET}";

            function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
            $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
            return $connection;
            }

            $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

            $tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);

            $fh = fopen($file, 'w') or die("can't open file");
            fwrite($fh, json_encode($tweets));
            fclose($fh);

            $tweets = json_decode(file_get_contents($file));
            echo json_encode($tweets);
        }
    }

    function getFacebook($user) {
        // Pulls a JSON feed of $user posts. Requires authentication and permission to view posts

        //let the browsers know the output is JSON
        header('content-type: application/json; charset=utf-8');
        //allow cross-site access
        header("access-control-allow-origin: *");

        $facebookuser = $user;
        $file = "cache/facebook/".$facebookuser."-posts.json";

        if (file_exists($file) && (time()-filemtime($file) < 15 * 60)) {
            $posts = json_decode(file_get_contents($file));
            echo json_encode($posts);
        } else {
            require 'facebook-src/facebook.php';
            $facebook = new Facebook(array(
              'appId'  => '{YOUR_APP_ID}',
              'secret' => '{YOUR_SECRET}',
            ));

            $posts = $facebook->api('/' . $facebookuser . '/posts');

            $fh = fopen($file, 'w') or die("can't open file");
            fwrite($fh, json_encode($posts));
            fclose($fh);

            $posts = json_decode(file_get_contents($file));
            echo json_encode($posts);
        }
    }

    function getYoutube($user) {
        // Pulls JSON feed of $user YouTube videos. Uses API v2
        // Doesn't require an API key, but less likely to hit service errors if you include one.
        // If you don't want to use an API key, make sure you remove it from the $request_url

        //let the browsers know the output is JSON
        header('content-type: application/json; charset=utf-8');
        //allow cross-site access
        header("access-control-allow-origin: *");

        $youtubeuser = $user;
        $file = "cache/youtube/".$youtubeuser."-videos.json";

        if (file_exists($file) && (time()-filemtime($file) < 15 * 60)) {
            $videos = json_decode(file_get_contents($file));
            echo json_encode($videos);
        } else {
            $api_key = "{YOUR_API_KEY}";
            $request_url = "http://gdata.youtube.com/feeds/api/videos?q=".$youtubeuser."&v=2&alt=jsonc&orderby=published&key=".$api_key;
            $json = file_get_contents($request_url, true);
            $videos = json_decode($json, true);

            $fh = fopen($file, 'w') or die("can't open file");
            fwrite($fh, json_encode($videos));
            fclose($fh);

            $videos = json_decode(file_get_contents($file));
            echo json_encode($videos);
        }
    }

    function getFlickr($user) {
        // Returns JSON feed of photo sets of $user. Requires API key.
        
        //let the browsers know the output is JSON
        header('content-type: application/json; charset=utf-8');
        //allow cross-site access
        header("access-control-allow-origin: *");

        $flickruser = $user;
        $file = "cache/flickr/".$flickruser."-photosets.json";

        if (file_exists($file) && (time()-filemtime($file) < 15 * 60)) {
            $photos = json_decode(file_get_contents($file));
            echo json_encode($photos);
        } else {
            $api_key = "{YOUR_API_KEY}";
            $user_url = "http://www.flickr.com/services/rest/?method=flickr.people.findByUsername&username=".$flickruser."&format=json&api_key=".$api_key."&nojsoncallback=1";
            $user_json = file_get_contents($user_url, true);
            $response = json_decode($user_json, true);
            $user_id = $response['user']['id'];

            $request_url = "http://www.flickr.com/services/rest/?method=flickr.photosets.getList&user_id=".$user_id."&format=json&nojsoncallback=1&api_key=".$api_key;
            $json = file_get_contents($request_url, true);
            $photosets = json_decode($json, true);

            $fh = fopen($file, 'w') or die("can't open file");
            fwrite($fh, json_encode($photosets));
            fclose($fh);

            $photosets = json_decode(file_get_contents($file));
            echo json_encode($photosets);
        }
    }

?>
