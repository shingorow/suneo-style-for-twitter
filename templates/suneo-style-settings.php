<?php
$inputs_protected = [
    ['label' => 'どちらでもいい', 'value' => 'ignore'],
    ['label' => '公開アカウントのみ', 'value' => 'open'],
    ['label' => '鍵アカウントのみ', 'value' => 'protected'],
];
?>
<div id="settings">
    <div class="setting-inner">
        <h3>設定</h3>
        <form method="POST">
            <?php wp_nonce_field($action, $name) ?>

            <h4>ログインの条件設定</h4>

            <div class="row">
                <div class="label">
                    <label>アカウントに鍵がかかっているか</label>
                </div>

                <div class="input">
                    <?php foreach ($inputs_protected as $input) : ?>
                        <p>
                            <label>
                                <input type="radio" name="protected" value="<?= $input['value'] ?>" id="lock" <?= $input['value'] === $protected ? 'checked' : '' ?>>
                                <?= $input['label'] ?>
                            </label>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row">
                <div class="label">
                    <label for="screen-name">
                        Twitter IDに含む文字列<br>
                        <small>
                            複数設定する場合は1行に1単語記入
                        </small>
                    </label>
                </div>
                <div class="input"><textarea name="screen_name" id="screen-name"><?= $screen_name ?></textarea></div>
            </div>

            <div class="row">
                <div class="label">
                    <label for="twitter-id">
                        表示名に含む文字列<br>
                        <small>
                            複数設定する場合は1行に1単語記入
                        </small>
                    </label>
                </div>
                <div class="input"><textarea name="twitter_name" id="twitter-id"><?= $twitter_name ?></textarea></div>
            </div>

            <div class="row">
                <div class="label">
                    <label for="description">
                        プロフィールに含む文字列<br>
                        <small>
                            複数設定する場合は1行に1単語記入
                        </small>
                    </label>
                </div>
                <div class="input"><textarea type="text" name="description" id="description"><?= $description ?></textarea></div>
            </div>

            <hr>

            <h4>表示設定</h4>

            <?php
            $pages = get_pages();
            ?>

            <div class="row">
                <div class="label"><label for="redirect-kicked-out">条件を満たさない場合のリダイレクト先</label></div>
                <div class="input">
                    <input type="text" name="redirect_kicked_out" value="<?= $redirect_kicked_out ?>" id="redirect-kicked-out" placeholder="http://yourdomain.com/loginfailed">
                </div>
            </div>

            <div>
                <button type="submit" class="button button-primary">設定を保存する</button>
            </div>

        </form>
    </div>
</div>