/**
 * @file
 * Joinup theme scripts.
 */

(function ($, Drupal) {
  Drupal.behaviors.deleteButton = {
    attach: function (context, settings) {
      $(context).find('#edit-delete').once('deleteButton').each(function () {
        $(this).addClass('button--default button--blue mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent');
      });
    }
  };

  Drupal.behaviors.silderSelect = {
    attach: function (context, settings) {
      $(context).find('.slider__select').once('sliderSelect').each(function () {
        var $select = $(this);
        var selectLength = $select.find('option').length;

        var $slider = $("<div id='slider' class='slider__slider'></div>").insertAfter($select).slider({
          min: 1,
          max: selectLength,
          range: "min",
          value: $select[ 0 ].selectedIndex + 1,
          change: function (event, ui) {
            $select.find('option').removeAttr('selected');
            $($select.find('option')[ui.value - 1]).attr('selected', 'selected');
          }
        });
      });
    }
  };

  Drupal.behaviors.alterTableDrag = {
    attach: function (context, settings) {
      $(context).find('.tabledrag-handle').once('alterTableDrag').each(function () {
        $(this).addClass('draggable__icon icon icon--draggable');
      });
    }
  };

  Drupal.behaviors.ajaxReload = {
    attach: function (context, settings) {
      $(context).find('form').once('ajaxReload').each(function () {
        $(document).ajaxComplete(function (event, xhr, settings) {
          componentHandler.upgradeAllRegistered();
        });
      });
    }
  }

  // Fix vertical tabs on the form pages.
  Drupal.behaviors.verticalTabsGrid = {
    attach: function (context, settings) {
      $(context).find('.vertical-tabs').once('verticalTabsGrid').each(function () {
        // Add mdl grid classes.
        $(this).find('.vertical-tabs__menu').addClass('mdl-cell--2-col');
        $(this).find('.vertical-tabs__panes').addClass('mdl-cell--8-col');
        $(this).addClass('mdl-grid');

        // Move description from pane to tab.
        $(this).find('.vertical-tabs__pane').each(
          function () {
            var summary = $(this).find('.vertical-tabs__details-summary').text();
            var summaryIndex = $(this).index();
            var $menuItem = $(this).closest('.vertical-tabs').find('.vertical-tabs__menu-item').get(summaryIndex - 1);
            $($menuItem).find('.vertical-tabs__menu-item-summary').text(summary);
          });
      });
    }
  }
})(jQuery, Drupal);