/**
 * WordPress SEO PRO 通用脚本。
 */
(function ($) {
	'use strict';

	/**
	 * 初始化评分环。
	 */
	function initScoreRing() {
		$('.score-ring').each(function () {
			var $ring = $(this);
			var $value = $ring.find('.score-value');
			var maxWidth = 100;
			var score = parseInt($ring.data('score'), 10) || 0;
			var circumference = 2 * Math.PI * 70; // 半径 70
			var offset = circumference - (score / 100) * circumference;

			$ring.css('stroke-dasharray', circumference);
			$ring.css('stroke-dashoffset', offset);

			$value.text(score);
		});
	}

	/**
	 * 初始化长度指示器。
	 */
	function initLengthIndicator() {
		$('.length-indicator').each(function () {
			var $wrap = $(this);
			var $input = $wrap.find('input, textarea');
			var max = parseInt($wrap.data('max') || 160, 10);
			var $current = $wrap.find('.current');

			var update = function () {
				var len = $input.val().length;
				$current.text(len);
			};

			$input.on('input', update);
			update();
		});
	}

	/**
	 * 初始化评分徽章。
	 */
	function initScoreBadges() {
		$('.audit-card-list .badge').each(function () {
			var $badge = $(this);
			var rating = $badge.data('rating');
			if (rating) {
				$badge.removeClass('high medium low');
				$badge.addClass(rating);
			}
		});
	}

	$(document).ready(function () {
		initScoreRing();
		initLengthIndicator();
		initScoreBadges();

		// 入口文本/标题初始化。
		$('.score-breakdown').html('<p class="description">' + wpseoPro.i18n.breakdown + '</p>');
	});
})(jQuery);
