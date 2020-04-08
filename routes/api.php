<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::post('user/sendcode', 'UserController@sendCode');
Route::post('user/register', 'UserController@register');
Route::post('user/login', 'UserController@login');
Route::post('user/huantest', 'UserController@huanTest');
Route::post('user/sendtoken', 'UserController@SendToken');
Route::post('user/facedetect', 'UserController@faceDetect');
Route::post('user/facecompare', 'UserController@faceCompare');
Route::post('user/avatar', 'UserController@userAvatarInfo');
Route::post('user/adduseravatar', 'UserController@addUserAvatar');
Route::post('user/adduseravatarasync', 'UserController@addUserAvatarAsync');
Route::post('user/uploadavatar', 'UserController@uploadAvatar');





Route::post('mine/index', 'MineController@index');
Route::post('mine/userinfo', 'MineController@userInfo');
Route::post('mine/myhome', 'MineController@myHome');
Route::post('mine/othershome', 'MineController@othersHome');
Route::post('mine/myfriends', 'MineController@myFriends');
Route::post('mine/blacklist', 'MineController@blackList');
Route::post('mine/setprivacy', 'MineController@setMyPrivacy');
Route::post('mine/blackoperate', 'MineController@blackOperate');
Route::post('mine/addordelfriend', 'MineController@addordelFriend');
Route::post('mine/edituserinfo', 'MineController@editUserInfo');
Route::post('mine/passset', 'MineController@passSet');
Route::post('mine/phonechange', 'MineController@phoneChange');
Route::post('mine/adduserarea', 'MineController@addUserArea');
Route::post('mine/useravatarlist', 'MineController@userAvatarList');
Route::post('mine/deluseravatar', 'MineController@delUserAvatar');
Route::post('mine/delmypublish', 'MineController@delMyPublish');
Route::post('mine/isfriend', 'MineController@isFriend');



Route::post('publish/homepage', 'PublishController@homePage');
Route::post('publish/userpublish', 'PublishController@userPublish');
Route::post('publish/getwordlists', 'PublishController@getWordLists');
Route::post('publish/searchwords', 'PublishController@searchWords');
Route::post('publish/accusate', 'PublishController@accusate');
Route::post('publish/userreply', 'PublishController@userReply');
Route::post('publish/usersetlove', 'PublishController@userSetLove');
Route::post('publish/test', 'PublishController@test');
Route::post('publish/voicetest', 'PublishController@voicetest');
Route::post('publish/userpublishasync', 'PublishController@userPublishAsync');
Route::post('publish/userreplypublishasync', 'PublishController@userReplyPublishAsync');


Route::post('message/getmessagelist', 'MessageController@getMessageList');
Route::post('message/getmessagereplylist', 'MessageController@getMessageReplyList');
Route::post('message/setreplyreaded', 'MessageController@setReplyReaded');
Route::post('message/getchatlist', 'MessageController@getChatList');
Route::post('message/geteachchatlist', 'MessageController@getEachChatList');
Route::post('message/delchat', 'MessageController@delChat');
Route::post('message/setchatreaded', 'MessageController@setChatReaded');
Route::post('message/getgroupuserdata', 'MessageController@getGroupUserData');
Route::post('message/userchatsystem', 'MessageController@userChatSystem');
Route::post('message/getusersystemdata', 'MessageController@getUserSystemData');
Route::post('message/setsystemnewsreaded', 'MessageController@setSystemNewsReaded');
Route::post('message/recordchatdetail', 'MessageController@recordChatDetail');


Route::post('backstage/getwordtypelist', 'BackStageController@getWordTypeList');
Route::post('backstage/addwordtype', 'BackStageController@addWordType');
Route::post('backstage/addwords', 'BackStageController@addWords');
Route::post('backstage/getwordlist', 'BackStageController@getWordList');
Route::post('backstage/getcomplaintlist', 'BackStageController@getComplaintList');
Route::post('backstage/userlist', 'BackStageController@userList');
Route::post('backstage/userfriend', 'BackStageController@userFriend');
Route::post('backstage/userpublishlist', 'BackStageController@userPublishList');
Route::post('backstage/delcomplaint', 'BackStageController@delComplaint');
Route::post('backstage/editword', 'BackStageController@editWord');
Route::post('backstage/editworddetail', 'BackStageController@editWordDetail');
Route::post('backstage/setwordstatus', 'BackStageController@setWordStatus');
Route::post('backstage/prohibition', 'BackStageController@prohibition');
Route::post('backstage/voiceexaminelist', 'BackStageController@voiceExamineList');
Route::post('backstage/voiceexamine', 'BackStageController@voiceExamine');
Route::post('backstage/voicereplyexaminelist', 'BackStageController@voiceReplyExamineList');
Route::post('backstage/avatarexaminelist', 'BackStageController@avatarExamineList');
Route::post('backstage/avatarexamine', 'BackStageController@avatarExamine');
Route::post('backstage/adduser', 'BackStageController@addUser');

/*
 * stat Api
 */
Route::post('stat/addUserStat', 'StatController@addUserStat');
Route::post('stat/activeUserStat', 'StatController@activeUserStat');
Route::post('stat/xzlcStat', 'StatController@xzlcStat');
Route::post('stat/appDownLoadStat', 'StatController@appDownLoadStat');
Route::post('stat/approveStat', 'StatController@approveStat');
Route::post('stat/avatarCheckStat', 'StatController@avatarCheckStat');
Route::post('stat/recordLogin', 'StatController@recordLogin');
Route::post('stat/recordAppDownLoad', 'StatController@recordAppDownLoad');
