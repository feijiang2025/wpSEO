/**
 * WordPress SEO PRO SEO 编辑器脚本。
 */
(function ($) {
	'use strict';

	var $editor = $('#wpseo-editor');
	var $form = $('#wpseo-editor-form');
	var postId = $editor.data('post-id');
	var $titleInput = $('#seo_title');
	var $descriptionInput = $('#metadescription');
	var $scoreRing = $('.score-ring');
	var $scoreValue = $scoreRing.find('.score-value');
	var $scoreBreakdown = $('#score-breakdown');

	/**
	 * 计算并更新评分。
	 */
	function updateScore() {
		var title = $titleInput.val();
		var desc = $descriptionInput.val();
		var score = 50;

		var titleLen = title.length;
		if (titleLen >= 30 && titleLen <= 60) {
			score += 15;
		} else if (titleLen >= 20 && titleLen < 30) {
			score += 5;
		} else {
			score += 5;
		}

		if (titleLen < 40 || titleLen > 60) {
			score -= 5;
		}

		if ($titleInput.val().trim()) {
			score += 5;
		}

		if ($descriptionInput.val().trim().length > 50) {
			score += 5;
		}

		score = Math.max(0, Math.min(100, score));

		$scoreRing.data('score', score);
		$scoreValue.text(score);

		// 动态生成评分项。
		var items = [];
		if (title.length > 0) {
			items.push('<div class="breakdown-item"><div class="breakdown-label">' + wpseoPro.i18n.titleLength + '</div><div class="breakdown-value">' + titleLen + '</div></div>');
		}
		if (desc.length > 0) {
			items.push('<div class="breakdown-item"><div class="breakdown-label">' + wpseoPro.i18n.descriptionLength + '</div><div class="breakdown-value">' + desc.length + '</div></div>');
		}

		$scoreBreakdown.html(items.join(''));
	}

	/**
	 * 保存 SEO 数据。
	 */
	function save() {
		var data = $form.serializeArray();
		var obj = {};
		$.each(data, function (i, field) {
			obj[field.name] = field.value;
		});

		obj.action = 'wpseo_pro_save_seo';
		obj.nonce = wpseoPro.nonce;

		$.post(wpseoPro.ajax_url, obj, function (response) {
			if (response && response.success) {
				// 刷新评分。
				updateScore();
				// 显示成功提示。
				$('.editor-actions').after('<div class="updated notice" style="margin-top:12px;"><p>' + wpseoPro.i18n.saved + '</p></div>');
			} else {
				$('.editor-actions').after('<div class="error" style="margin-top:12px;"><p>' + wpseoPro.i18n.saveFailed + '</p></div>');
			}
		}).fail(function () {
			$('.editor-actions').after('<div class="error" style="margin-top:12px;"><p>' + wpseoPro.i18n.saveFailed + '</p></div>');
		});
	}

	// 事件绑定。
	$(document).ready(function () {
		$titleInput.on('input', updateScore);
		$descriptionInput.on('input', updateScore);

		$form.submit(function (e) {
			e.preventDefault();
			save();
		});

		$('#wpseo-save-async').on('click', save);

		// 初始化评分。
		updateScore();
	});
})(jQuery);
