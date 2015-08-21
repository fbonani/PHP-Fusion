<?php

// removed album_order
// always pick the latest increment.
// the shuffling do it at move up and down.
// best consistency and less debugging.

$data = array(
	"album_id" => 0,
	"album_title" => "",
	"album_description" => "",
	"album_access" => "",
	"album_language" => "",
	"album_image" => "",
	"album_thumb" => "",
	"album_order" => dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "")+1
);

if (isset($_POST['save_album'])) {
	$data = array(
		"album_id" => form_sanitizer($_POST['album_id'], 0, "album_id"),
		"album_title" => form_sanitizer($_POST['album_title'], "", "album_title"),
		"album_description" => form_sanitizer($_POST['album_description'], "", "album_description"),
		"album_access" => form_sanitizer($_POST['album_access'], "", "album_access"),
		"album_language" => form_sanitizer($_POST['album_language'], "", "album_language"),
		"album_order" => form_sanitizer($_POST['album_order'], "", "album_order"),
		"album_image" => "",
		"album_thumb" => "",
	);
	if (empty($data['album_order'])) {
		$data['album_order'] = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_PHOTO_ALBUMS."
				".(multilang_table("PG") ? "where album_language='".LANGUAGE."'" : "").""), 0)+1;
	}
	if (defender::safe()) {
		if (!empty($_FILES['album_image'])) {
			$upload = form_sanitizer($_FILES['album_image'], "", "album_image");
			if (!empty($upload['error'])) {
				$data['album_image'] = $upload['image_name'];
				$data['album_thumb1'] = $upload['thumb1_name'];
				$data['album_thumb2'] = $upload['thumb2_name'];
			}
		}
	}
	if (defender::safe()) {
		if (dbcount("(album_id)", DB_PHOTO_ALBUMS, "album_id='".intval($data['album_id'])."'")) {
			// update album
			$result = dbquery_order(DB_PHOTO_ALBUMS, $data['album_order'], 'album_order', $data['album_id'],
									'album_id', FALSE, FALSE, TRUE, 'album_language', 'update');
			addNotice('success', $locale['album_0011']);
			redirect(FUSION_SELF.$aidlink);
		} else {
			// create album
			$result = dbquery_order(DB_PHOTO_ALBUMS, $data['album_order'], 'album_order', 0,
									"album_id", FALSE, FALSE, TRUE, 'album_language', 'save');
			dbquery_insert(DB_PHOTO_ALBUMS, $data, "save");
			addNotice('success', $locale['album_0012']);
			redirect(FUSION_SELF.$aidlink);
		}
	}
}


// edit features - add more in roadmap.
// add features to purge all album photos and it's administration
// add features to move all album photos to another album.

echo openform('albumform', 'post', FUSION_REQUEST, array('enctype' => true, 'class' => 'm-t-20'));
echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
echo form_hidden('album_id', '', $data['album_id']);
echo form_text('album_title', $locale['album_0001'], $data['album_title'], array('placeholder' => $locale['album_0002'],
	'inline' => true,
	'required' => true));
echo form_textarea('album_description', $locale['album_0003'], $data['album_description'], array('placeholder' => $locale['album_0004'],
	'inline' => 1));
if ($data['album_image']) {
	echo "<div class='well'>\n";
	//$img_path = self::get_virtual_path($data['album_id']).rtrim($this->upload_settings['thumbnail_folder'], '/')."/".$data['album_thumb'];
	//echo "<img class='img-responsive' style='margin:0 auto;' src='$img_path' alt='".$data['album_title']."'/>\n";
	echo "</div>\n";
} else {

	$album_upload_settings = array(
		"upload_path" => INFUSIONS."gallery/photos/",
		'thumbnail_folder'=>'thumbs',
		'thumbnail' => true,
		'thumbnail_w' =>  $gll_settings['thumb_w'],
		'thumbnail_h' =>  $gll_settings['thumb_h'],
		'thumbnail_suffix' =>'_t1',
		'thumbnail2'=> true,
		'thumbnail2_w' 	=>  $gll_settings['photo_w'],
		'thumbnail2_h' 	=>  $gll_settings['photo_h'],
		'thumbnail2_suffix' => '_t2',
		'max_width'		=>	$gll_settings['photo_max_w'],
		'max_height'	=>	$gll_settings['photo_max_h'],
		'max_byte'		=>	$gll_settings['photo_max_b'],
		'multiple' => 0,
		'delete_original' => false,
		"inline"=>true,
		"template" => "modern",
		"class" => "m-b-0",
	);
	echo form_fileinput('album_image', $locale['album_0007'], "", $album_upload_settings);
	echo "<div class='m-b-10 col-xs-12 col-sm-offset-3'>".sprintf($locale['album_0008'], parsebytesize($gll_settings['photo_max_b']),
										str_replace(',', ' ', ".jpg,.gif,.png"),
										$gll_settings['photo_max_w'], $gll_settings['photo_max_h'])."</div>\n";
}
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
echo form_select('album_access', $locale['album_0005'], $data['album_access'], array('options' => fusion_get_groups(),'inline' => true));
echo form_select('album_language', $locale['album_0006'], $data['album_language'], array('options' => fusion_get_enabled_languages(),'inline' => TRUE));
echo form_text('album_order', $locale['album_0009'], $data['album_order'], array("type" => "number",'inline' => true));
echo "</div>\n</div>\n";
echo form_button('save_album', $locale['album_0010'], $locale['album_0010'], array('class' => 'btn-success btn-sm m-r-10'));
echo closeform();