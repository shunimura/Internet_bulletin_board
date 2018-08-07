<!DECTYPE html>
<html>    
    <head>
        <meta charset = "UTF-8">
        <title>mission_4-1-4</title>
    </head>
</html>

<!DECTYPE php>
<?php
    /*** データベースへの接続 ***/
    $dsname = 'データベース名';
    $dnuser = 'ユーザ名';
    $dnpassword = 'パスワード';
    try{
        $pdo = new PDO($dsname, $dnuser, $dnpassword);
        
        /*** テーブルの作成 ***/
        $sql = "CREATE TABLE tbtest16"
        ."("
        ."tbpostnumber INT,"
        ."tbname char(32),"
        ."tbcomment TEXT,"
        ."tbdate TEXT,"
        ."tbpassword TEXT"
        .");";
        $stmt = $pdo -> query($sql);

        $postdate = date("Y/m/d H:i:s");

        /*** 前のページからのデータの受け取り ***/
        $username = $_POST['username'];
        $comment = $_POST['comment'];
        $password = $_POST['password'];

        $deletenumber = $_POST['deletenumber'];
        $dpassword = $_POST['dpassword'];

        $editnumber = $_POST['editnumber'];
        $epassword = $_POST['epassword'];

        $editing = $_POST['editing'];
        $epcorrect = $_POST['epcorrect'];
        $editIsOk = $_POST['editIsOk'];
        $numOfDelete = $_POST['numOfDelete'];

        /*** パスワードチェック ***/
        if(isset($editnumber)||isset($deletenumber)){
            
                $sql = "SELECT * FROM tbtest16";
                $result = $pdo -> query($sql);
                $textdata = $result -> fetchAll();
                $i = 0;
                foreach($textdata as $temp){

                    $dpcorrect = 0;
                    $epcorrect = 0;
                    
                    if($deletenumber == $temp['tbpostnumber'] && $dpassword == $temp['tbpassword']){
                        $dpcorrect = 1;
                        break;
                    }
                    else if($editnumber == $temp['tbpostnumber'] && $epassword == $temp['tbpassword']){
                        $epcorrect = 1;
                        break;
                    }
                    $i++;

                }

        }


        /*** コメントがあればテキストファイルに入力 ***/
        if($comment != ''){

            if($username == ''){
                $username = '名無しさん@php楽しい';
            }

            if( $editIsOk != 1 ){
                $sql = "SELECT * FROM tbtest16 ORDER BY tbpostnumber DESC";
                $result = $pdo -> query($sql);
                $postnumber = 1;// テーブルに中身がない場合の対応
                foreach($result as $temp){
                    $postnumber = $temp['tbpostnumber']+1;
                    break;
                }
                    
                $sql = $pdo -> prepare("INSERT INTO tbtest16"
                ."(tbpostnumber, tbname, tbcomment, tbdate, tbpassword)"
                ." VALUES (:tbpostnumber, :tbname, :tbcomment, :tbdate, :tbpassword)"
                );
                $postnumber+=$numOfDelete;
                $sql -> bindParam(':tbpostnumber', $postnumber, PDO::PARAM_STR);
                $sql -> bindParam(':tbname', $username, PDO::PARAM_STR);
                $sql -> bindParam(':tbcomment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':tbdate', $postdate, PDO::PARAM_STR);
                $sql -> bindParam(':tbpassword', $password, PDO::PARAM_STR);


                $sql -> execute();

                if ($comment == '完成！'){
                    echo 'おめでとう！<br>';
                }

                echo 
                    'ご入力ありがとうございます。<br>',
                    '入力された内容「',$_POST['comment'],'」を保存しました。<br>',
                    $postdate,'<br><br>';
                
                $numOfDelete = 0;
            }
        }
        else if($comment == '' && $editnumber == '' && $deletenumber == ''){
            echo 'コメントを入力してください。<br>';
        }  


        /*** 編集用パスワードが合っていれば編集 ***/
        if( ( $editing != '' || $editing != 0 ) && $editIsOk == 1){
            echo "Edit Data Send Start<br>";
            $sql = "UPDATE tbtest16 SET tbname = :tbname, tbcomment = :tbcomment ,tbpassword = :tbpassword WHERE tbpostnumber = $editing";
            $stmt = $pdo -> prepare($sql);
            $params = array(':tbname' => $username, ':tbcomment' => $comment, ':tbpassword' => $password);
            $stmt -> execute($params);


            if ($comment == '完成！'){
                echo 'おめでとう！<br>';
            }

            echo 
                'ご入力ありがとうございます。<br>',
                '投稿番号',$editing,'の内容を変更しました。<br>'
                ,$postdate,'<br><br>';

            $editing = 0;
            unset($editnumber);
            $epcorrect = 0;

        }

        /******/

        if($epcorrect == '1'){
            
            echo '投稿番号'.$editnumber.'の投稿内容の編集を開始します。<br>';

            $editing = $editnumber;
            $sql = "SELECT * FROM tbtest16 WHERE tbpostnumber = $editnumber";
            $result = $pdo -> query($sql);
            foreach($result as $temp){
                $username = $temp['tbname'];
                $comment = $temp['tbcomment'];
                $password = $temp['tbpassword'];
            }

        }
        else{
            if($epcorrect == '0' && isset($editnumber) && $comment =='' && $deletenumber == '' 
                && (isset($comment)==false||$comment=='') && (isset($username)==false||$username=='') && (isset($password)==false||$password=='')  ){
                    if(isset($editing)){
                        ;
                    }
                    else{
                        echo '編集エラー：パスワードが違います。<br>';
                    }
            }
        }

        /*** 削除対象番号の入力があれば、対象の投稿を削除 ***/

        if($deletenumber != ''){

            if($dpcorrect == 1){
                echo "Delete Start<br>";
                $sql = "DELETE FROM tbtest16 WHERE tbpostnumber = $deletenumber";
                $pdo -> query($sql);

                $sql = "SELECT * FROM tbtest16 ORDER BY tbpostnumber DESC";
                $result = $pdo -> query($sql);
                foreach($result as $temp){
                    if($temp['tbpostnumber'] == $deletenumber){
                        $numOfDelete++; // 最新投稿が削除された場合
                    }
                }

                    
                }
            else{
                echo '削除エラー：パスワードが違います。<br>';
            }
            
        }
        /******/

        
        /*** 入力したデータの確認 ***/
        $sql = "SELECT * FROM tbtest16 ORDER BY tbpostnumber ASC";
        $results = $pdo -> query($sql);
        foreach ($results as $row){
            echo $row['tbpostnumber'].',';
            echo $row['tbname'].',';
            echo $row['tbcomment'].'<br>';
        }

    }
    catch(PDOException $e) {
        print "Error: " .$e -> getMessage()."<br/>";
        die();
    }

?>

<!DECTYPE html>
<html>
    <body>
        <form action = "mission_4-1-4.php"  method = "post">
            <br>
            <?php if( ($editnumber != 0 || $editnumber != '') && $epcorrect == 1 ) {echo '編集モード<br>';}else{echo '新規モード<br>';}?>
            <input type = "text"  name = "username"  placeholder = "名前"  value = "<?php if ($editnumber != '' ){echo $username;} ?>" >
            <br>
            <input type = "text"  name = "comment"  placeholder = "コメント（必須）"  value = "<?php if ($editnumber != '' ){echo $comment;} ?>">
            <br>
            <input type = "text"  name = "password"  placeholder = "パスワード"  value = "<?php if ($editnumber != '' ){echo $password;} ?>">
            <input type = "submit"  value = "送信">
            <br>
            <br>

            <input type = "text"  name = "deletenumber"  placeholder = "削除対象番号（半角数字）">
            <br>
            <input type = "text"  name = "dpassword"  placeholder = "パスワード">
            <input type = "submit"  value = "送信">
            <br>
            <br>

            <input type = "text"  name = "editnumber"  placeholder = "編集対象番号（半角数字）">
            <br>
            <input type = "text"  name = "epassword"  placeholder = "パスワード">
            <input type = "submit"  value = "送信">
            <br>
            
            <input type = "hidden"  name = "editing"  value = "<?php if ($editnumber != '' ){ echo $editing = $editnumber; } ?>">
            <input type = "hidden"  name = "editIsOk"  value = "<?php if ($epcorrect == 1){ $epcorrect = 0; echo $editIsOk = 1; } ?>">
            <input type = "hidden"  name = "epcorrect"  value = "<?php if ($epcorrect != '' ){ echo $epcorrect; } ?>">
            <input type = "hidden"  name = "numOfDelete"  value = "<?php if ($numOfDelete == '' ){$numOfDelete = 0;} echo $numOfDelete; ?>">
            
        </form>
    </body>
</html>