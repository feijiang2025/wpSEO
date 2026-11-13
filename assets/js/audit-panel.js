/**
 * WordPress SEO PRO 审计面板脚本。
 */
(function ($) {
	'use strict';

	var $audit = $('#wpseo-audit-result');
	var postId = $audit.data('post-id');
	var $form = $('form[method="get"]');

	/**
	 * 渲染关键词密度列表。
	 */
	function renderKeywordDensity(data) {
		var html = '';
		if (data && data.keyword_density && data.keyword_density.length) {
			$.each(data.keyword_density, function (kw, rate) {
				var rating = 'low';
				if (rate >= 3) {
					rating = 'high';
				} else if (rate >= 1) {
					rating = 'medium';
				}
				html += '<li>' +
						'<span class="audit-card-item-label">' + $('<div/>').text(kw).html() + '</span>' +
						'<span class="audit-card-item-value">' + rate + '</span>' +
						'<span class="audit-card-item-note">' + $('<div/>').text(rate + '%').html() + '</span>' +
						'<span class="badge ' + rating + '" data-rating="' + rating + '">' + wpseoPro.i18n.rating + '</span>' +
					'</li>';
			});
		} else {
			html += '<li><span class="audit-card-item-label">—</span><span class="audit-card-item-value">—</span></li>';
		}
		return html;
	}

	/**
	 * 渲染高频词列表。
	 */
	function renderCommonWords(data) {
		var html = '';
		if (data && data.common_words && data.common_words.length) {
			$.each(data.common_words, function (i, w) {
				html += '<li><span class="audit-card-item-label">' + $('<div/>').text(w).html() + '</span></li>';
			});
		} else {
			html += '<li><span class="audit-card-item-label">—</span></li>';
		}
		return html;
	}

	/**
	 * 触发审计。
	 */
	function runAudit() {
		if (!postId) {
			return;
		}

		var data = {
			action: 'wpseo_pro_audit_save',
			nonce: wpseoPro.nonce,
			post_id: postId
		};

		$.post(wpseoPro.ajax_url, data, function (response) {
			if (response && response.success) {
				renderAudit(response.data);
			}
		}).fail(function () {
			$audit.html('<p class="wpseo-pro-empty">' + wpseoPro.i18n.failed + '</p>');
		});
	}

	/**
	 * 渲染审计结果。
	 */
	function renderAudit(data) {
		var $container = $audit;

		// 总分环。
		var score = data.score || 0;
		var rating = 'low';
		if (score >= 70) {
			rating = 'high';
		} else if (score >= 40) {
			rating = 'medium';
		}

		$container.html(
			'<div class="score-ring-wrap">' +
				'<div class="score-ring" data-score="' + score + '">' +
					'<span class="score-value">' + score + '</span>' +
					'<span class="score-label">' + wpseoPro.i18n.score + '</span>' +
					'<div class="score-badge ' + rating + '">' + wpseoPro.i18n.rating + '</div>' +
				'</div>' +
			'</div>' +

			// 概览。
			'<div class="audit-cards">' +
				'<div class="audit-card">' +
					'<h3 class="audit-card-title">' + wpseoPro.i18n.overview + '</h3>' +
					'<ul class="audit-card-list">' +
						'<li>' +
							'<span class="audit-card-item-label">' + wpseoPro.i18n.title + '</span>' +
							'<span class="audit-card-item-value">' + (data.title_len || '—') + '</span>' +
							'<span class="audit-card-item-note">' + wpseoPro.i18n.characters + '</span>' +
						'</li>' +
						'<li>' +
							'<span class="audit-card-item-label">' + wpseoPro.i18n.thumbnail + '</span>' +
							'<span class="audit-card-item-value">' + (data.has_image ? '已有' : '未找到') + '</span>' +
						'</li>' +
						'<li>' +
							'<span class="audit-card-item-label">' + wpseoPro.i18n.internalLinks + '</span>' +
							'<span class="audit-card-item-value">' + (data.internal_links || '—') + '</span>' +
							'<span class="audit-card-item-note">个</span>' +
						'</li>' +
					'</ul>' +
				'</div>' +

				// 标题结构。
				'<div class="audit-card">' +
					'<h3 class="audit-card-title">' + wpseoPro.i18n.headings + '</h3>' +
					'<ul class="audit-card-list">' +
						'<li>' +
							'<span class="audit-card-item-label">H1</span>' +
							'<span class="audit-card-item-value">' + (data.h1_count || 0) + '</span>' +
							'<span class="audit-card-item-note">' + (data.details && data.details.missing_h1 ? '建议至少一个' : '') + '</span>' +
						'</li>' +
						'<li>' +
							'<span class="audit-card-item-label">H2</span>' +
							'<span class="audit-card-item-value">' + (data.h2_count || 0) + '</span>' +
							'<span class="audit-card-item-note">' + (data.details && data.details.missing_h2 ? '建议至少一个' : '') + '</span>' +
						'</li>' +
						'<li>' +
							'<span class="audit-card-item-label">H3</span>' +
							'<span class="audit-card-item-value">' + (data.h3_count || 0) + '</span>' +
							'<span class="audit-card-item-note">' + (data.details && data.details.too_many_h3 ? '过多' : '') + '</span>' +
						'</li>' +
					'</ul>' +
				'</div>' +

				// 关键词密度。
				'<div class="audit-card">' +
					'<h3 class="audit-card-title">' + wpseoPro.i18n.keywordDensity + '</h3>' +
					'<ul class="audit-card-list">' +
						renderKeywordDensity(data) +
					'</ul>' +
				'</div>' +

				// 高频词。
				'<div class="audit-card">' +
					'<h3 class="audit-card-title">' + wpseoPro.i18n.commonWords + '</h3>' +
					'<ul class="audit-card-list">' +
						renderCommonWords(data) +
					'</ul>' +
				'</div>' +
			'</div>'
		);

		// 刷新评分环。
		setTimeout(function () {
			initScoreRing();
		}, 50);
	}

	/**
	 * 初始化评分环。
	 */
	function initScoreRing() {
		$('.score-ring').each(function () {
			var $ring = $(this);
			var $value = $ring.find('.score-value');
			var score = parseInt($ring.data('score'), 10) || 0;
			var circumference = 2 * Math.PI * 70;
			var offset = circumference - (score / 100) * circumference;

			$ring.css('stroke-dasharray', circumference);
			$ring.css('stroke-dashoffset', offset);
			$value.text(score);
		});
	}

	// 初始化。
	$(document).ready(function () {
		if (postId) {
			runAudit();
		}
	});

	// 通过侧栏搜索。
	$form.submit(function (e) {
		e.preventDefault();
		var val = $form.find('input[name="post_id"]').val();
		if (val) {
			postId = parseInt(val, 10);
			$audit.html('<p class="description">' + wpseoPro.i18n.scanning + '</p>');
			runAudit();
		}
	});
})(jQuery);
