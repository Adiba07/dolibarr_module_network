<?php
/**
 * Copyright (C) @@YEAR@@ ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require '../config.php';
dol_include_once('/network/class/network.class.php');

$langs->load('network@network');

$fk_source = (int) GETPOST('fk_source');
$sourcetype = GETPOST('sourcetype');
?>

$(document).ready(function() {


    $('#network-container').appendTo('#id-right');

	<?php
    if (!empty($user->rights->network->write)) {
    ?>
        var $button = $('#network-add-comment input[name=btcomment]').click(function () {
            addComment();
        });

        function addComment() {
            var link = $('#network-writer input[name=network_link]').val();
            var target = $('#network-writer input[name=network_target]').val();

            $.ajax({
                url: '<?php echo dol_buildpath('/network/script/interface.php', 1); ?>'
                , dataType: "JSON"
                , data: {
                    action: "addComment"
                    , link: link
                    , json: 1
                    , fk_source: <?php echo $fk_source; ?>
                    , sourcetype: "<?php echo $sourcetype; ?>"
                    , target: target
                }
                , method: 'POST'
            }).done(function (data) {
                NetworkLoadComment();
                $('#network-writer input[name=network_link]').val("");
                $('#network-writer input[name=network_target], #network-writer input[name=search_network_target]').val("");
            });

        }

	<?php
    }
	?>

    <?php
    if (!empty($user->rights->network->read)) {
    ?>
        NetworkLoadComment();
    <?php
    }
    ?>
});

<?php
if (!empty($user->rights->network->read)) {
?>
    function NetworkLoadComment(start, limit) {
        if (!start) start = 0;
        if (!limit) limit = 10;

        $.ajax({
            url: '<?php echo dol_buildpath('/network/script/interface.php', 1) ?>'
            , dataType: 'JSON'
            , data: {
                action: "getComments"
                , json: 1
                , fk_source: <?php echo $fk_source; ?>
                , sourcetype: "<?php echo $sourcetype; ?>"
                , start: start
                , limit: limit
            }
        }).done(function (data) {
            if (start > 0) {
                $('#network-comments div.showMore').remove();
                NetworkAddCommentsIntoDOM(data, start, limit, false);
            } else {
                NetworkAddCommentsIntoDOM(data, start, limit, true);
            }
        });
    }

    function NetworkAddCommentsIntoDOM(data, start, limit, setempty)
    {
        if (typeof data !== 'undefined')
        {
            if (setempty) $('#network-comments').empty();

            for (let i in data) {
                if (i >= limit) {
                    $('#network-comments').append('<div class="comm showMore" start="'+start+'" limit="'+limit+'" style="text-align:center"><a href="javascript:;" onclick="NetworkLoadComment('+(start + limit)+')">&#x25BC; <?php echo dol_escape_js($langs->trans('NetworkShowMore')); ?> &#x25BC;</a></div>');
                    break;
                } else {
                    let $comment = $('<div id="network-comment-'+data[i].rowid+'" class="comm" commid="'+data[i].rowid+'">');

                    $comment.append('<span class="rel badge network_badge network-badge-link">'+data[i].link+'</span>');

                    $comment.append(data[i].url);

                    <?php
                    if (!empty($user->rights->network->delete)) {
                    ?>
                        $comment.append('<div class="delete"><a href="javascript:networkRemoveComment('+data[i].rowid+')"><?php echo img_delete(); ?></a></div>');
                    <?php
                    }
                    ?>

                    $comment.append('<div class="date">'+data[i].author+' - '+data[i].date+'</div>');

                    if ($comment.find('.classfortooltip').length > 0) {
                        // Copy of ajaxdirtree.php
                        $comment.find('.classfortooltip').tooltip({
                            show: { collision: "flipfit", effect:'toggle', delay:50 },
                            hide: { delay: 50 }, 	/* If I enable effect:'toggle' here, a bug appears: the tooltip is shown when collpasing a new dir if it was shown before */
                            tooltipClass: "mytooltip",
                            content: function () {
                                return $(this).prop('title');		/* To force to get title as is */
                            }
                        });
                    }

                    $('#network-comments').append($comment);
                }
            }
        }
    }
<?php
}
?>

<?php
if (!empty($user->rights->network->delete)) {
?>
    function networkRemoveComment(commid) {
        if (window.confirm("<?php echo dol_escape_js($langs->transnoentitiesnoconv('NetworkComfirmDelete')) ?>")) {
            $.ajax({
                url: '<?php echo dol_buildpath('/network/script/interface.php', 1); ?>'
                , dataType: "JSON"
                , data: {
                    action: "deleteComment"
                    , json: 1
                    , id: commid
                }
            }).done(function (data) {
                $('#network-comment-' + commid).remove();
            });
        }
    }
<?php
}
