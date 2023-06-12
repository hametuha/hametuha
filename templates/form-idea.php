<form id="new-idea-form">
	<?php if ( $id ) : ?>
		<input type="hidden" name="post_id" id="new-idea-id" value="<?php echo esc_attr( $id ); ?>"/>
	<?php endif; ?>
	<div class="form-group">
		<label for="new-idea-name">タイトル案</label>
		<input type="text" class="form-control" placeholder="ex. 永遠の偏差値ゼロ" name="new-idea-name" id="new-idea-name"
			   value="<?php echo esc_attr( $title ); ?>"/>
	</div>
	<div class="form-group">
		<label for="new-idea-content">あらすじ</label>
		<textarea rows="5" class="form-control"
				  placeholder="ex. 元零戦の搭乗員である佐藤函火粉（はこひこ）が現代に蘇り、高校生活を送ることになる。しかし、彼の成績は一向に上がらず……"
				  name="new-idea-content" id="new-idea-content"><?php echo esc_textarea( $content ); ?></textarea>
	</div>
	<div class="form-group">
		<label for="new-idea-genre">ジャンル</label>
		<select class="form-control" name="new-idea-genre" id="new-idea-genre">
			<?php
			foreach (
				get_terms( 'post_tag', [
					'meta_query' => [
						[
							'name'  => 'tag_type',
							'value' => 'idea',
						],
					],
				] ) as $term
			) :
				?>
				<option
					value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $term->term_id == $genre ); ?>><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="form-group">
		<label><input type="checkbox" name="new-idea-privacy" id="new-idea-privacy"
					  value="1" <?php checked( $private ); ?>/> 非公開</label>
		<span class="helper-block">非公開のものを特定の人にだけ見せることは可能です。</span>
	</div>
	<div class="row">
		<div class="col-xs-6">
			<button type="button" class="btn btn-default btn-block" data-dismiss="modal">キャンセル</button>
		</div>
		<div class="col-xs-6">
			<input type="submit" class="btn btn-primary btn-block" value="<?php echo $id ? '更新' : '作成'; ?>"/>
		</div>
	</div>
</form>
