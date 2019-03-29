<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <title>Sample - A3RT Text Summarization API</title>
</head>

<body>
    <?php
    function get_summaries($linenumber, $sentences)
    {
        #$apikey = getenv('a3rt_text_summarization_apikey');
        $apikey = "ここにAPI Keyを記入";

        $post_data = array(
            'apikey' => $apikey,
            'sentences' => $sentences,
            'linenumber' => $linenumber,
        );

        $curl = curl_init("https://api.a3rt.recruit-tech.co.jp/text_summarization/v1/");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $summaries = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($summaries);
    }

    if (isset($_POST['srctext'])) {
        $SEPARATOR = '。';
        $summary_short = '';
        $summary_medium = '';
        $reqtext = '';
        $srctext = $_POST['srctext'];
        $srctext = str_replace(array("\n", "\r"), '', $srctext);
        $sentences = explode($SEPARATOR, $srctext);
        foreach ($sentences as $key => $sentence) {
            $sentence = trim($sentence);
            if ((mb_strlen($sentence) < 200) && ($sentence != '')) {
                $reqtext .= $sentence . $SEPARATOR;
            }
            if (
                (mb_substr_count($reqtext, $SEPARATOR) >= 10)
                || (($reqtext != '') && ($key >= count($sentences) - 1))
            ) {
                $summary_short .= implode($SEPARATOR, get_summaries(1, $reqtext)->summary) . $SEPARATOR;
                $summary_medium .= implode($SEPARATOR, get_summaries(5, $reqtext)->summary) . $SEPARATOR;
                $reqtext = '';
            }
        }
        $json_summary_short = json_encode($summary_short);
        $json_summary_medium = json_encode($summary_medium);
        $json_srctext = json_encode($srctext);
    }
    ?>
    <script>
        function switchText(viewmode) {
            var doc = document.getElementById('text');
            switch (viewmode) {
                case "short":
                    doc.textContent = <?php echo $json_summary_short; ?>;
                    break;
                case "medium":
                    doc.textContent = <?php echo $json_summary_medium; ?>;
                    break;
                case "full":
                default:
                    doc.textContent = <?php echo $json_srctext; ?>;
                    break;
            }
        }
    </script>
    <div class="container-fluid">
        <form class="form-horizontal" method="post" action="#">
            <div class="form-group">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <a class="btn btn-outline-primary" href="#" role="button" onclick="switchText('full')">多(全文)</a>
                    <a class="btn btn-outline-primary" href="#" role="button" onclick="switchText('medium')">中</a>
                    <a class="btn btn-outline-primary" href="#" role="button" onclick="switchText('short')">少</a>
                </div>
        </form>
    </div>
    <div class="border border-primary" id="text">
        表示する文章量を選択してください。
    </div>
</body>

</html> 