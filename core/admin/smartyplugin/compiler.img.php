<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.tplheader.php
 * Type:     compiler
 * Name:     tplheader
 * Purpose:  Output header containing the source file name and
 *           the time it was compiled.
 * -------------------------------------------------------------
 */
function tpl_compiler_img($attrs, &$smarty) {
    $imgLib = array(
/*- begin -*/

'images/bundle/addcate.gif'=>'width:16px;height:16px;background-position:0 -0px;',
'images/bundle/afresh.gif'=>'width:16px;height:16px;background-position:0 -16px;',
'images/bundle/afresh_off.gif'=>'width:16px;height:16px;background-position:0 -32px;',
'images/bundle/arrow-down.gif'=>'width:7px;height:5px;background-position:0 -48px;',
'images/bundle/arrow-left.gif'=>'width:5px;height:9px;background-position:0 -53px;',
'images/bundle/arrow-right.gif'=>'width:5px;height:9px;background-position:0 -62px;',
'images/bundle/arrow-up.gif'=>'width:7px;height:5px;background-position:0 -71px;',
'images/bundle/blue-dot.gif'=>'width:12px;height:12px;background-position:0 -76px;',
'images/bundle/btn_add.gif'=>'width:14px;height:14px;background-position:0 -88px;',
'images/bundle/btn_appstore.gif'=>'width:16px;height:15px;background-position:0 -102px;',
'images/bundle/btn_download.gif'=>'width:15px;height:16px;background-position:0 -117px;',
'images/bundle/btn_edit.gif'=>'width:15px;height:16px;background-position:0 -133px;',
'images/bundle/btn_get_world.gif'=>'width:16px;height:16px;background-position:0 -149px;',
'images/bundle/clock.gif'=>'width:16px;height:16px;background-position:0 -165px;',
'images/bundle/closeBtn.gif'=>'width:16px;height:16px;background-position:0 -181px;',
'images/bundle/close_btn.gif'=>'width:17px;height:17px;background-position:0 -197px;',
'images/bundle/delecate.gif'=>'width:16px;height:16px;background-position:0 -214px;',
'images/bundle/delete.gif'=>'width:12px;height:10px;background-position:0 -230px;',
'images/bundle/disabled.gif'=>'width:16px;height:16px;background-position:0 -240px;',
'images/bundle/downinfo.gif'=>'width:13px;height:14px;background-position:0 -256px;',
'images/bundle/downinfo_off.gif'=>'width:13px;height:14px;background-position:0 -270px;',
'images/bundle/editcate.gif'=>'width:16px;height:16px;background-position:0 -284px;',
'images/bundle/enabled.gif'=>'width:16px;height:16px;background-position:0 -300px;',
'images/bundle/finder_add.gif'=>'width:24px;height:24px;background-position:0 -316px;',
'images/bundle/finder_back.gif'=>'width:24px;height:24px;background-position:0 -340px;',
'images/bundle/finder_data_refresh.gif'=>'width:24px;height:24px;background-position:0 -364px;',
'images/bundle/finder_date.gif'=>'width:22px;height:24px;background-position:0 -388px;',
'images/bundle/finder_drop_arrow.gif'=>'width:16px;height:16px;background-position:0 -412px;',
'images/bundle/finder_drop_arrow_close.gif'=>'width:16px;height:16px;background-position:0 -428px;',
'images/bundle/finder_drop_arrow_up.gif'=>'width:16px;height:16px;background-position:0 -444px;',
'images/bundle/finder_filter.gif'=>'width:26px;height:24px;background-position:0 -460px;',
'images/bundle/finder_io.gif'=>'width:25px;height:24px;background-position:0 -484px;',
'images/bundle/finder_list_edit.gif'=>'width:16px;height:16px;background-position:0 -508px;',
'images/bundle/finder_mail.gif'=>'width:22px;height:24px;background-position:0 -524px;',
'images/bundle/finder_massive.gif'=>'width:22px;height:24px;background-position:0 -548px;',
'images/bundle/finder_movetop.gif'=>'width:20px;height:24px;background-position:0 -572px;',
'images/bundle/finder_print.gif'=>'width:22px;height:24px;background-position:0 -596px;',
'images/bundle/finder_print_style.gif'=>'width:24px;height:24px;background-position:0 -620px;',
'images/bundle/finder_refresh.gif'=>'width:22px;height:24px;background-position:0 -644px;',
'images/bundle/finder_search_btn.gif'=>'width:19px;height:17px;background-position:0 -668px;',
'images/bundle/finder_sheet.gif'=>'width:21px;height:24px;background-position:0 -685px;',
'images/bundle/finder_tag.gif'=>'width:18px;height:24px;background-position:0 -709px;',
'images/bundle/finder_tpl.gif'=>'width:24px;height:24px;background-position:0 -733px;',
'images/bundle/finder_trash.gif'=>'width:17px;height:24px;background-position:0 -757px;',
'images/bundle/finder_trash_rev.gif'=>'width:22px;height:24px;background-position:0 -781px;',
'images/bundle/finder_view_trash.gif'=>'width:17px;height:24px;background-position:0 -805px;',
'images/bundle/folder_page.gif'=>'width:16px;height:16px;background-position:0 -829px;',
'images/bundle/gears_ico.gif'=>'width:16px;height:16px;background-position:0 -845px;',
'images/bundle/handle-hide.gif'=>'width:12px;height:12px;background-position:0 -861px;',
'images/bundle/handle-show.gif'=>'width:12px;height:12px;background-position:0 -873px;',
'images/bundle/help-about.gif'=>'width:16px;height:16px;background-position:0 -885px;',
'images/bundle/hidden.gif'=>'width:24px;height:16px;background-position:0 -901px;',
'images/bundle/ico-buy.gif'=>'width:14px;height:18px;background-position:0 -917px;',
'images/bundle/ico-chat.gif'=>'width:16px;height:16px;background-position:0 -935px;',
'images/bundle/ico_help.gif'=>'width:16px;height:16px;background-position:0 -951px;',
'images/bundle/icon_asc.gif'=>'width:13px;height:12px;background-position:0 -967px;',
'images/bundle/icon_asc_2.gif'=>'width:13px;height:12px;background-position:0 -979px;',
'images/bundle/icon_desc.gif'=>'width:13px;height:12px;background-position:0 -991px;',
'images/bundle/icon_desc_2.gif'=>'width:13px;height:12px;background-position:0 -1003px;',
'images/bundle/mceico_0.gif'=>'width:16px;height:16px;background-position:0 -1015px;',
'images/bundle/mceico_1.gif'=>'width:16px;height:16px;background-position:0 -1031px;',
'images/bundle/mceico_10.gif'=>'width:16px;height:16px;background-position:0 -1047px;',
'images/bundle/mceico_11.gif'=>'width:16px;height:16px;background-position:0 -1063px;',
'images/bundle/mceico_12.gif'=>'width:16px;height:16px;background-position:0 -1079px;',
'images/bundle/mceico_13.gif'=>'width:16px;height:16px;background-position:0 -1095px;',
'images/bundle/mceico_14.gif'=>'width:16px;height:16px;background-position:0 -1111px;',
'images/bundle/mceico_15.gif'=>'width:16px;height:16px;background-position:0 -1127px;',
'images/bundle/mceico_16.gif'=>'width:16px;height:16px;background-position:0 -1143px;',
'images/bundle/mceico_17.gif'=>'width:16px;height:16px;background-position:0 -1159px;',
'images/bundle/mceico_18.gif'=>'width:16px;height:16px;background-position:0 -1175px;',
'images/bundle/mceico_19.gif'=>'width:16px;height:16px;background-position:0 -1191px;',
'images/bundle/mceico_2.gif'=>'width:16px;height:16px;background-position:0 -1207px;',
'images/bundle/mceico_20.gif'=>'width:16px;height:16px;background-position:0 -1223px;',
'images/bundle/mceico_21.gif'=>'width:16px;height:16px;background-position:0 -1239px;',
'images/bundle/mceico_22.gif'=>'width:16px;height:16px;background-position:0 -1255px;',
'images/bundle/mceico_23.gif'=>'width:16px;height:16px;background-position:0 -1271px;',
'images/bundle/mceico_24.gif'=>'width:16px;height:16px;background-position:0 -1287px;',
'images/bundle/mceico_25.gif'=>'width:16px;height:16px;background-position:0 -1303px;',
'images/bundle/mceico_26.gif'=>'width:16px;height:16px;background-position:0 -1319px;',
'images/bundle/mceico_27.gif'=>'width:16px;height:16px;background-position:0 -1335px;',
'images/bundle/mceico_28.gif'=>'width:16px;height:16px;background-position:0 -1351px;',
'images/bundle/mceico_29.gif'=>'width:16px;height:16px;background-position:0 -1367px;',
'images/bundle/mceico_3.gif'=>'width:16px;height:16px;background-position:0 -1383px;',
'images/bundle/mceico_30.gif'=>'width:16px;height:16px;background-position:0 -1399px;',
'images/bundle/mceico_31.gif'=>'width:16px;height:16px;background-position:0 -1415px;',
'images/bundle/mceico_32.gif'=>'width:16px;height:16px;background-position:0 -1431px;',
'images/bundle/mceico_33.gif'=>'width:16px;height:16px;background-position:0 -1447px;',
'images/bundle/mceico_34.gif'=>'width:16px;height:16px;background-position:0 -1463px;',
'images/bundle/mceico_35.gif'=>'width:16px;height:16px;background-position:0 -1479px;',
'images/bundle/mceico_36.gif'=>'width:16px;height:16px;background-position:0 -1495px;',
'images/bundle/mceico_37.gif'=>'width:16px;height:16px;background-position:0 -1511px;',
'images/bundle/mceico_38.gif'=>'width:16px;height:16px;background-position:0 -1527px;',
'images/bundle/mceico_39.gif'=>'width:16px;height:16px;background-position:0 -1543px;',
'images/bundle/mceico_4.gif'=>'width:16px;height:16px;background-position:0 -1559px;',
'images/bundle/mceico_40.gif'=>'width:16px;height:16px;background-position:0 -1575px;',
'images/bundle/mceico_41.gif'=>'width:16px;height:16px;background-position:0 -1591px;',
'images/bundle/mceico_42.gif'=>'width:16px;height:16px;background-position:0 -1607px;',
'images/bundle/mceico_43.gif'=>'width:16px;height:16px;background-position:0 -1623px;',
'images/bundle/mceico_44.gif'=>'width:16px;height:16px;background-position:0 -1639px;',
'images/bundle/mceico_45.gif'=>'width:16px;height:16px;background-position:0 -1655px;',
'images/bundle/mceico_46.gif'=>'width:16px;height:16px;background-position:0 -1671px;',
'images/bundle/mceico_47.gif'=>'width:16px;height:16px;background-position:0 -1687px;',
'images/bundle/mceico_48.gif'=>'width:30px;height:16px;background-position:0 -1703px;',
'images/bundle/mceico_5.gif'=>'width:16px;height:16px;background-position:0 -1719px;',
'images/bundle/mceico_6.gif'=>'width:16px;height:16px;background-position:0 -1735px;',
'images/bundle/mceico_7.gif'=>'width:16px;height:16px;background-position:0 -1751px;',
'images/bundle/mceico_8.gif'=>'width:16px;height:16px;background-position:0 -1767px;',
'images/bundle/mceico_9.gif'=>'width:16px;height:16px;background-position:0 -1783px;',
'images/bundle/minus.gif'=>'width:12px;height:12px;background-position:0 -1799px;',
'images/bundle/new_window.gif'=>'width:16px;height:16px;background-position:0 -1811px;',
'images/bundle/notebook_pencil.gif'=>'width:16px;height:16px;background-position:0 -1827px;',
'images/bundle/ok.gif'=>'width:24px;height:16px;background-position:0 -1843px;',
'images/bundle/opguide_ico.gif'=>'width:16px;height:16px;background-position:0 -1859px;',
'images/bundle/page_edit.gif'=>'width:16px;height:16px;background-position:0 -1875px;',
'images/bundle/page_new.gif'=>'width:16px;height:16px;background-position:0 -1891px;',
'images/bundle/page_script.gif'=>'width:16px;height:16px;background-position:0 -1907px;',
'images/bundle/plus.gif'=>'width:12px;height:12px;background-position:0 -1923px;',
'images/bundle/return.gif'=>'width:10px;height:10px;background-position:0 -1935px;',
'images/bundle/save.gif'=>'width:16px;height:16px;background-position:0 -1945px;',
'images/bundle/saveaddsame.gif'=>'width:16px;height:16px;background-position:0 -1961px;',
'images/bundle/savereturn.gif'=>'width:16px;height:16px;background-position:0 -1977px;',
'images/bundle/savesetdef.gif'=>'width:16px;height:16px;background-position:0 -1993px;',
'images/bundle/savetolist.gif'=>'width:16px;height:16px;background-position:0 -2009px;',
'images/bundle/showcate.gif'=>'width:16px;height:16px;background-position:0 -2025px;',
'images/bundle/sidemaps-action.gif'=>'width:16px;height:16px;background-position:0 -2041px;',
'images/bundle/sidemaps-articles.gif'=>'width:16px;height:16px;background-position:0 -2057px;',
'images/bundle/sidemaps-custompage.gif'=>'width:16px;height:16px;background-position:0 -2073px;',
'images/bundle/sidemaps-goodpackage.gif'=>'width:16px;height:16px;background-position:0 -2089px;',
'images/bundle/sidemaps-goodsCat.gif'=>'width:16px;height:16px;background-position:0 -2105px;',
'images/bundle/sidemaps-layouts.gif'=>'width:16px;height:16px;background-position:0 -2121px;',
'images/bundle/sidemaps-links.gif'=>'width:16px;height:16px;background-position:0 -2137px;',
'images/bundle/sidemaps-page.gif'=>'width:16px;height:16px;background-position:0 -2153px;',
'images/bundle/sidemaps-pageurl.gif'=>'width:16px;height:16px;background-position:0 -2169px;',
'images/bundle/sitemap-closed.gif'=>'width:16px;height:16px;background-position:0 -2185px;',
'images/bundle/sitemapclosed.gif'=>'width:9px;height:9px;background-position:0 -2201px;',
'images/bundle/sitemapopened.gif'=>'width:9px;height:9px;background-position:0 -2210px;',
'images/bundle/sitemaps-opened.gif'=>'width:16px;height:16px;background-position:0 -2219px;',
'images/bundle/spage_editing.gif'=>'width:16px;height:16px;background-position:0 -2235px;',
'images/bundle/success.gif'=>'width:16px;height:16px;background-position:0 -2251px;',
'images/bundle/tag_all.gif'=>'width:9px;height:9px;background-position:0 -2267px;',
'images/bundle/tag_none.gif'=>'width:9px;height:9px;background-position:0 -2276px;',
'images/bundle/tag_part.gif'=>'width:9px;height:9px;background-position:0 -2285px;',
'images/bundle/tips.gif'=>'width:18px;height:18px;background-position:0 -2294px;',
'images/bundle/to_down.gif'=>'width:16px;height:16px;background-position:0 -2312px;',
'images/bundle/to_up.gif'=>'width:16px;height:16px;background-position:0 -2328px;',
'images/bundle/tupian_1.gif'=>'width:17px;height:17px;background-position:0 -2344px;',
'images/bundle/tupian_2.gif'=>'width:17px;height:17px;background-position:0 -2361px;',
'images/bundle/tupian_3.gif'=>'width:17px;height:17px;background-position:0 -2378px;',
'images/bundle/tupian_4.gif'=>'width:17px;height:17px;background-position:0 -2395px;',
'images/bundle/tupian_5.gif'=>'width:17px;height:17px;background-position:0 -2412px;',
'images/bundle/tupian_6.gif'=>'width:17px;height:17px;background-position:0 -2429px;',
'images/bundle/tupian_7.gif'=>'width:17px;height:17px;background-position:0 -2446px;',
'images/bundle/view_detailed.gif'=>'width:28px;height:28px;background-position:0 -2463px;',
'images/bundle/view_text.gif'=>'width:28px;height:28px;background-position:0 -2491px;',
'images/bundle/visible.gif'=>'width:24px;height:16px;background-position:0 -2519px;',
'images/bundle/zoom_btn.gif'=>'width:18px;height:19px;background-position:0 -2535px;',

/*- end -*/
    );
    if(isset($attrs['tag'])){
        if($attrs['tag']{0}=='\'' || $attrs['src']{0}=='"'){
            $tag = substr($attrs['tag'],1,-1);
        }else{
            $tag = $attrs['tag'];
        }
    }else{
        $tag = 'img';
    }

    $html = '?><'.$tag.' ';
    if($attrs['src']{0}=='\'' || $attrs['src']{0}=='"'){
        $src = substr($attrs['src'],1,-1);
    }else{
        $src = $attrs['src'];
    }

    if($attrs['class']){
        $attrs['class'] = '"imgbundle '.(($attrs['class']{0}=='"' || $attrs['class']{0}=='\'')?substr($attrs['class'],1,-1):$attrs['class']).'"';
    }else{
        $attrs['class'] = '"imgbundle"';
    }

    if(isset($imgLib[$src])){
        $attrs['src'] =  '"images/transparent.gif"';
        if($attrs['style']){
            $attrs['style'] = '"'.$imgLib[$src].';'.(($attrs['style']{0}=='"' || $attrs['style']{0}=='\'')?substr($attrs['style'],1,-1):$attrs['style']).'"';
        }else{
            $attrs['style'] = '"'.$imgLib[$src].'"';
        }
    }else{
        if($attrs['style']){
            $attrs['style'] = '"background-image: none;'.(($attrs['style']{0}=='"' || $attrs['style']{0}=='\'')?substr($attrs['style'],1,-1):$attrs['style']).'"';
        }else{
            $attrs['style'] = '"background-image: none;"';
        }
    }
    foreach($attrs as $k=>$v){
        $html.=$k.'='.((strpos($v,'$')===false)?$v:'"<?php echo '.$v.';?>"').' ';
    }
    return $html.' /><?php ';
}
?>