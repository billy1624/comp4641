<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMP4641 - Group4: Labeling Training Samples</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        div.emotion-options > label {
            margin-top: 6px;
            margin-bottom: 2px;
        }
        div.emotion-options > label.form-check-label {
            margin-top: 0px;
        }
        div.emotion-options > .form-check {
            font-size: 16px;
        }
        div.emotion-options {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php
    $emotions = [
        'Irrelevant Tweet'      => [ 'irrelevant'=>999 ],
        'Neutral'               => [ 'neutral'=>99 ],
        'Joy'                   => [ 'ecstasy'=>1,    'serenity'=>2 ],
        'Trust'                 => [ 'admiration'=>3, 'acceptance'=>4 ],
        'Fear'                  => [ 'terror'=>5,     'apprehension'=>6 ],
        'Surprise'              => [ 'amazement'=>7,  'distraction'=>8 ],
        'Sadness'               => [ 'grief'=>9,      'pensiveness'=>10 ],
        'Disgust'               => [ 'loathing'=>11,  'boredom'=>12 ],
        'Anger'                 => [ 'rage'=>13,      'annoyance'=>14 ],
        'Anticipation'          => [ 'vigilance'=>15, 'interest'=>16 ],
        'Others'                => [ 'optimism'=>17, // Anticipation + Joy
                                     'love'=>18, // Joy + Trust
                                     'submission'=>19, // Trust + Fear
                                     'awe'=>20, // Fear + Surprise
                                     'disapproval'=>21, // Surprise + Sadness
                                     'remorse'=>22, // Sadness + Disgust
                                     'contempt'=>23, // Disgust + Anger
                                     'aggressiveness'=>24, // Anger + Anticipation
                                     'shame'=>25, // Fear + Sadness
                                     'pride'=>26, // Anger + Joy
                                   ],
    ];

    
    $emotions_to_num = [];
    foreach ($emotions as $k => $v) {
        foreach ($v as $emotion => $num) {
            $emotions_to_num[$emotion] = $num;
        }
    }

    $conn = new mysqli("127.0.0.1", "root", "root", "comp4641");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // printf("Initial character set: %s\n", $conn->character_set_name());
    if (!$conn->set_charset("utf8mb4")) {
        printf("Error loading character set utf8mb4: %s\n", $conn->error);
        exit();
    } else {
        // printf("Current character set: %s\n", $conn->character_set_name());
    }

    $user = trim($_POST['user']) ?? "";

    if ( !empty($_POST['submit']) && $_POST['submit'] == 'submit' && !empty($_POST['id']) ) {
        $id = $_POST['id'];
        $voted = $conn->query(sprintf("SELECT id FROM votes WHERE user = '%s' AND tweets_id = '%s' LIMIT 1", $conn->real_escape_string($user), $conn->real_escape_string($id)));
        if ($voted->num_rows == 0) {
            $emotion_submit = [];
            foreach ($emotions_to_num as $k => $v) {
                if (!empty($_POST[$k]) && intval($_POST[$k]) === $v) {
                    $emotion_submit[] = $v;
                }
            }
            if (count($emotion_submit) > 0) {
                $emotion_submit = json_encode($emotion_submit);
                $sql = "INSERT INTO votes (tweets_id, user, emotions, date) VALUES ('%s', '%s', '%s', '%s')";
                $conn->query(sprintf($sql, $conn->real_escape_string($id), $conn->real_escape_string($user), $conn->real_escape_string($emotion_submit), date('Y-m-d H:i:s')));
            }
        }
    }

    $labeled_tweets_count = $conn->query(sprintf("SELECT count(*) as count FROM votes WHERE votes.user = '%s'", $conn->real_escape_string($user)));
    if (!$labeled_tweets_count) {
        printf("Error: %s\n", $conn->error);
    }
    $labeled_tweets_count = $labeled_tweets_count->fetch_assoc();
    $labeled_tweets_count = $labeled_tweets_count['count'];

    $labeled_tweets = $conn->query(sprintf("SELECT votes.*, tweets.id AS tweets_id, tweets.is_quote, tweets.text, tweets.quoted_text FROM votes JOIN tweets ON tweets.id = votes.tweets_id WHERE votes.user = '%s' ORDER BY id DESC LIMIT 20", $conn->real_escape_string($user)));
    if (!$labeled_tweets) {
        printf("Error: %s\n", $conn->error);
    }
    // $labeled_tweets = $labeled_tweets->fetch_assoc();
    // var_dump($labeled_tweets);

    $tweet_id = null;
    $max_tweet_id = $conn->query("SELECT MAX(id) as id FROM tweets");
    $max_tweet_id = $max_tweet_id->fetch_assoc()['id'];

    $priority_tweets = $conn->query(sprintf("SELECT tweets_id, COUNT(*) AS num_vote FROM votes GROUP BY tweets_id HAVING num_vote < 3 AND tweets_id NOT IN (SELECT tweets_id FROM votes WHERE votes.user = '%s')", $conn->real_escape_string($user)));
    $priority_tweets = $priority_tweets->fetch_assoc();
    // $priority_tweets['tweets_id'] = null;
    if ( !empty($priority_tweets['tweets_id']) ) {
        $tweet_id = $priority_tweets['tweets_id'];
    } else {
        do {
            $tweet_id = rand(1, $max_tweet_id);
            $labeled = $conn->query(sprintf("SELECT * FROM votes WHERE user = '%s' AND tweets_id = '%s'", $conn->real_escape_string($user), $conn->real_escape_string($tweet_id)));
            if (!$labeled) {
                printf("Error: %s\n", $conn->error);
            }
        } while ($labeled->num_rows > 0);
    }


    $tweets = $conn->query(sprintf("SELECT * FROM tweets WHERE id = '%s'", $conn->real_escape_string($tweet_id)));
    if (!$tweets) {
        printf("Error: %s\n", $conn->error);
    }
    $tweets = $tweets->fetch_assoc();
    // var_dump($tweets);

    $conn->close();
?>

<div class="container">
    <br>
    <h1>Labeling Training Dataset</h1>
    <hr>

    <?php if ($user) { ?>
        <h2>Tweet to be Labeled...</h2>
        <p><b>id:</b> <?php echo $tweets['id'] ?></p>
        <?php if ($tweets['is_quote'] == 'True')  { ?>
            <p><b>This is a quote.</b></p>
            <p><b>quoted_text:</b> <?php echo $tweets['quoted_text'] ?></p>
            <?php if (empty($tweets['original_tweet'])) { ?>
                <p><b>text:</b> <?php echo $tweets['text'] ?></p>
                <p><b>original_tweet:</b> **None**</p>
            <?php } else { ?>
                <p><b>original_tweet:</b> <?php echo $tweets['original_tweet'] ?></p>
            <?php } ?>
        <?php } else if ($tweets['is_retweet'] == 'True')  { ?>
            <p><b>This is a retweet.</b></p>
            <?php if (empty($tweets['original_tweet'])) { ?>
                <p><b>text:</b> <?php echo $tweets['text'] ?></p>
                <p><b>original_tweet:</b> **None**</p>
            <?php } else { ?>
                <p><b>original_tweet:</b> <?php echo $tweets['original_tweet'] ?></p>
            <?php } ?>
        <?php } else { ?>
            <p><b>text:</b> <?php echo $tweets['text'] ?></p>
        <?php } ?>
        <hr>
        <?php /*
        <pre><?php print_r($tweets) ?></pre>
        <hr>
        */ ?>
    <?php } ?>



    <form action="label.php" method="post">
        <?php if ($user) { ?>
            <h2>Your Options...</h2>
            <div class="form-group">
                <h5>Emotion(s):</h5>
                <small class="form-text text-muted">Please check the emotion(s) that best describe the Tweet above.</small>
            </div>
            <div class="form-group row">
                <?php foreach ($emotions as $title => $e) { ?>
                    <div class="col-md-3 emotion-options">
                        <label><b><u><?php echo (strpos($title,'+')!==false) ? null : $title ?></u></b></label>
                        <?php foreach ($e as $emotion => $num) { ?>
                            <div class="form-check">
                                <input class="orm-check-input" type="checkbox" id="<?php echo $emotion ?>" name="<?php echo $emotion ?>" value="<?php echo $num ?>">
                                <label class="form-check-label" for="<?php echo $emotion ?>"> <?php echo ucfirst($emotion) ?></label>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="form-group">
            <h5>Username:</h5>
            <input class="form-control" type="text" id="user" name="user" value="<?php echo $user ?: null ?>"><label>
            <small class="form-text text-muted">Please remember this username, and keep using the same username to label the dataset.</small>
            <input type="hidden" name="id" value=<?php echo $tweets['id'] ?>>
        </div>
        <div class="form-group">
            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>

    <br>
    <hr>

    <?php if ($user) { ?>
        <h2>Your Last 20 Labeled Tweets...</h2>
        <p>You have labeled <?php echo number_format($labeled_tweets_count, 0) ?> out of <?php echo number_format($max_tweet_id, 0) ?> (<?php echo number_format($labeled_tweets_count/$max_tweet_id*100.0, 5) ?>%) Tweets.</p>
        <br>
        <?php $count = 1; ?>
        <?php while ($tweets = $labeled_tweets->fetch_assoc()) { ?>
            <p>
                #<?php echo $count++ ?><br>
                <b>date:</b> <?php echo $tweets['date'] ?><br>
                <b>votes.id:</b> <?php echo $tweets['id'] ?><br>
                <b>id:</b> <?php echo $tweets['tweets_id'] ?><br>
                <?php if ($tweets['is_quote'] == 'True')  { ?>
                    <b>This is a quote.</b><br>
                    <b>quoted_text:</b> <?php echo $tweets['quoted_text'] ?><br>
                    <b>original_tweet:</b> <?php echo $tweets['original_tweet'] ?><br>
                <?php } else if ($tweets['is_retweet'] == 'True')  { ?>
                    <b>This is a retweet.</b><br>
                    <b>original_tweet:</b> <?php echo $tweets['original_tweet'] ?><br>
                <?php } else { ?>
                    <b>text:</b> <?php echo $tweets['text'] ?><br>
                <?php } ?>
                <b>emotions:</b> <?php 
                foreach (json_decode($tweets['emotions']) as $i => $emotion) {
                    if ($i > 0) {
                        echo ', ';
                    }
                    echo ucfirst(array_search($emotion, $emotions_to_num));
                }
                ?><br>
            </p>
            <br>
        <?php } ?>
    <?php } ?>
</div>

</body>
</html>