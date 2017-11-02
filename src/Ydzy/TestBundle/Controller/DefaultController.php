<?php
namespace Ydzy\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
	
    public function indexAction()
    {
        return $this->render('YdzyTestBundle:Default:index.html.twig');
    }


    public function menuAction()
    {
        return $this->render('YdzyTestBundle:Default:menu.html.twig');
    }
	

    public function recommendedAction()
    {
		$params = array(
				array(
					'name' => '推荐列表',
					'status' => 'id:商品编号，content:内容，image_url1:封面图，price:价钱，category:类别',
					'url' => 'api/recommended/retrieveByFilter',
					'params' => array(
					                   '开始的条目'=>'start',
					                   '要加载的条目'=>'num'
					                 )
				),
		      array(
		          'name' => '推荐详情',
					'status' => '',
					'url' => 'api/recommended/info',
					'params' => array(
									''=>'recommended_id'
					                 )
		      ),
		    array(
		        'name' => '发布推荐',
		        'status' => '成功返回插入的id',
		        'url' => 'api/recommended/add',
		        'params' => array(
		            '类别的id'=>'cid',
		            '详情内容'=>'content',
		            '价钱'=>'price',
		            '列表页图片,默认第一张,选择缩略图'=>'image_url',
		            '图片的id拼成的字符串，例如2,3,4'=>'image_ids'
		        )
		    ),
		    array(
		        'name' => '类别列表',
		        'status' => '',
		        'url' => 'api/recommended/categoryList',
		        'params' => array(
		        )
		    ),
		      array(
		          'name' => '是否点击不感兴趣和想要购买',
					'status' => 'buy=0没有购买过 buy=1已经购买过了;unlike=0没有点击不感兴趣，unlike=1不感兴趣',
					'url' => 'api/recommended/buyOrNot',
					'params' => array(
									''=>'recommended_id'
					                 )
		      ),
		      array(
		          'name' => '我想购买',
					'status' => '',
					'url' => 'api/recommended/buy',
					'params' => array(
									''=>'recommended_id'
					                 )
		      ),
		      array(
		          'name' => '我不感兴趣',
					'status' => '',
					'url' => 'api/recommended/unlike',
					'params' => array(
									''=>'recommended_id'
					                 )
		      ),
		      array(
		          'name' => '获得想买这个的人的列表',
					'status' => '',
					'url' => 'api/recommended/retrieveWantBuyList',
					'params' => array(
					               '商品的id'=>'recommended_id',
									'开始条目'=>'start',
					                'num'=>'num'
					                 )
		      ),
		    
		      array(
		          'name' => '删除单条',
					'status' => '',
					'url' => 'api/recommended/del',
					'params' => array(
									''=>'recommended_id'
					                 )
		      )
			);

        return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function bbsAction()
    {
    	$params = array(


    			array(
    					'name' => '获得微信支付信息（官方统一下单接口）',
    					'status' => ' ',
    					'url' => 'api/bbs/createWeixinPay',
    					'params' => array(
    							'1三星 2五星 3三星升五星'=>'mark'
    					)
    			),
        	    array(
        	        'name' => '获得支付宝支付信息(价格和唯一订单号)',
        	        'status' => ' ',
        	        'url' => 'api/bbs/createPay',
        	        'params' => array(
        	            '1三星 2五星 3三星升五星'=>'mark'
        	        )
        	    ),
    			array(
    					'name' => '发布帖子',
    					'status' => ' 401:你什么也不是，并且超管也没有开启普通会员发布权限  402:超管没开放vip5的普通会员发布权限  403:你必须要登陆才能看里面的内容! 405:超管没开放vip3的普通会员发布权限  406:你是vip3，不能发布vip5的东西 ',
    					'url' => 'api/bbs/add',
    					'params' => array(
    							'标题'=>'title',
    							'内容'=>'content',
    							'0三星 1五星'=>'mark',
    							'图片id拼接字符串，逗号分隔'=>'image_ids'
    					)
    			),
    			array(
    					'name' => '获得帖子列表',
    					'status' => '400:没有值返回啊！传对参数   401:你什么也不是，并且超管也没有开启普通会员查看权限  402:超管没开放vip5的普通会员查看权限  403:你必须要登陆才能看里面的内容! 405:超管没开放vip3的普通会员查看权限  406:你是vip3，不能查看vip5的东西 ',
    					'url' => 'api/bbs/retrieveByFilter',
    					'params' => array(
    							'0三星 1五星'=>'mark',
    							'开始条目'=>'start',
    							'num'=>'num'
    					)
    			),
    			array(
    					'name' => '获得帖子详情',
    					'status' => '这个需要穿token，返回的字段里collection需要登陆判断，返回1表示已经收藏，0表示为收藏',
    					'url' => 'api/bbs/info',
    					'params' => array(
    							'帖子id'=>'id'
    					)
    			),
    			array(
    					'name' => '评价帖子',
    					'status' => '',
    					'url' => 'api/bbs/comment',
    					'params' => array(
    							'帖子id'=>'id',
    					        '评论内容'=>'content'
    					)
    			),
    			array(
    					'name' => '帖子评论列表',
    					'status' => '',
    					'url' => 'api/bbs/commentList',
    					'params' => array(
    							'帖子id'=>'id',
    					        '0获取最新的 1获取历史'=>'mark',
    					        '开始条数'=>'start',
    					        '需要加载条数'=>'num'
    					)
    			),
    			array(
    					'name' => '帖子收藏(取消收藏)',
    					'status' => '',
    					'url' => 'api/bbs/collection',
    					'params' => array(
    							'帖子id'=>'id',
    					        '0取消收藏 1添加收藏'=>'mark'
    					)
    			),
    			array(
    					'name' => '我的帖子收藏列表',
    					'status' => '',
    					'url' => 'api/bbs/collectionList',
    					'params' => array(
    							'开始条数'=>'start',
    					        '需要加载条数'=>'num'
    					)
    			),
    			array(
    					'name' => '删除我的帖子',
    					'status' => '',
    					'url' => 'api/bbs/delmy',
    					'params' => array(
    							'帖子的id'=>'bbs_id'
    					)
    			)
    
    	);
    
    	return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function userAction(){
        $params = array(
        	array(
        			'name'=>'推送测试',
        			'status'=>'',
        			'url' => 'api/user/push',
        			'params' => array(
        				'registration'=>'registration',
        				'内容'=>'content'
        				)
        	),
            array(
                'name'=>'用户注册',
                'status'=>'401--手机号不能为空,402--密码不能为空；415--手机号注册过，416--用户昵称已经被使用，403--验证码不正确，200--成功',
                'url' => 'api/user/register',
                'params' => array(
                    '手机号'=>'phone',
                    '密码'=>'password',
                    '验证码，测试均使用9977'=>'validate',
                    '昵称，不填初始值为手机号'=>'username'
                )
            ),
            array(
                'name'=>'用户登陆',
                'status'=>'返回json：登陆成功,返回用户信息，403用户不存在，405表示密码错误，406用户已经被禁用，401没填手机号 402没填密码',
                'url' => 'api/user/login',
                'params' => array(
                    '手机号或者用户名'=>'phone',
                    '密码'=>'password',
					'极光推送registration_id'=>'registration'
                )
            ),
            array(
                'name'=>'用户注销',
                'status'=>'header里传入要注销的token',
                'url' => 'api/user/logout',
                'params' => array(
                )
            ),
            array(
					'name'=>'获得注册和忘记密码验证码',
					'status'=>'403--手机号已经被注册过  401--手机号不能为空 200--发送成功 500--发送失败 415--更改密码时手机号没有注册过 416--更改密码时手机号已经被禁用',
					'url' => 'api/user/getTelValidate',
					'params' => array(
								'手机号'=>'phone',
					              'mark=0注册1忘记密码，默认不填写mark=0'=>'mark',
													)
				),
            array(
					'name'=>'忘记密码',
					'status'=>'403--验证码不正确  401没填手机号 402没填密码，405用户不存在，406用户已经被禁用',
					'url' => 'api/user/forgetPwd',
					'params' => array(
								'手机号'=>'phone',
					            '验证码，测试均使用9977'=>'validate',
					            '新密码'=>'newpassword'
													)
				),
            array(
					'name'=>'重置密码',
					'status'=>'403--未登录 405--旧密码出错 200--成功',
					'url' => 'api/user/repwd',
					'params' => array(
					            '旧密码'=>'oldpassword',
					            '新密码'=>'newpassword'
													)
				),
            array(
                'name'=>'获得个人信息(或者根据user_id获取用户信息)',
                'status'=>'mark--0普通用户1商户，icon--头像，grade--0普通 1三星 2五星，buy_date--购买会员时间，last_login--上次登陆时间，create_date--用户创建时间',
                'url' => 'api/user/profile',
                'params' => array(
                    'user_id为空表示获得当前登陆者信息'=>'user_id',
                		'手机号'=>'phone'
                )
            ),
		      array(
		          'name' => 'somebody想购买的列表',
					'status' => '403--未登录并且没有填写user_id',
					'url' => 'api/user/retrieveWantBuyList',
					'params' => array(
									'用户id'=>'user_id',
					                'start'=>'start',
					                 'num'=>'num'
					                 )
		      ),
            array(
                'name'=>'更改当前用户（或者user_id）的信息',
                'status'=>'403未登录并且没有填写user_id',
                'url' => 'api/user/changeProfile',
                'params' => array(
                    '用户的user_id，可以为空，表示修改的是当前登陆者的'=>'user_id',
                    '头像路径'=>'icon',
                    '密码'=>'password',
                    '用户名username'=>'username',
                    '地址'=>'location',
                    '会员等级：0普通 1三星 2五星'=>'grade',
                    '0普通用户1商户'=>'mark',
                	'qq'=>'qq',
                	'email'=>'email',
                	'真实姓名'=>'truename'
                )
            ),
            array(
                'name'=>'获得当前的拍卖',
                'status'=>'返回值grade表示最低权限，0普通会员，1表示三星，2表示五星',
                'url' => 'api/user/chatGroup',
                'params' => array(
                    
                )
            ),
            array(
                'name'=>'生成一个新的订单',
                'status'=>'',
                'url' => 'api/user/createOrder',
                'params' => array(
                		'推荐的id'=>'publish_id',
                		'订单的价格'=>'money'
                    
                )
            ),
            array(
                'name'=>'获得我的订单',
                'status'=>'',
                'url' => 'api/user/myOrder',
                'params' => array(
                		'start'=>'start',
                		'num'=>'num'
                    
                )
            ),
            array(
                'name'=>'获得订单info',
                'status'=>'id:商品编号;money:定金;price:售价',
                'url' => 'api/order/info',
                'params' => array(
                		'id'=>'id'
                    
                )
            ),
            array(
                'name'=>'微信订单支付',
                'status'=>'',
                'url' => 'api/user/createWeixinPay',
                'params' => array(
                		'order_id'=>'order_id',
                        '联系方式'=>'phone',
                        '联系地址'=>'address'
                    
                )
            ),
            array(
                'name'=>'支付宝订单支付',
                'status'=>'',
                'url' => 'api/user/createAlipay',
                'params' => array(
                		'order_id'=>'order_id',
                        '联系方式'=>'phone',
                        '联系地址'=>'address'
                    
                )
            ),
            array(
                'name'=>'我收到的所有的信息',
                'status'=>'',
                'url' => 'api/user/allMessagesToMe',
                'params' => array(
                    
                )
            ),
            array(
                'name'=>'删除我收到的信息',
                'status'=>'',
                'url' => 'api/user/delMessage',
                'params' => array(
                    'id'=>'id'
                )
            ),
            array(
                'name'=>'更新信息阅读时间',
                'status'=>'',
                'url' => 'api/user/updateReadTime',
                'params' => array(
                    'message信息的id'=>'id'
                )
            ),
            array(
                'name'=>'未读的消息数',
                'status'=>'',
                'url' => 'api/user/unreadNum',
                'params' => array(
                )
            )
    );
        
        return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function manageAction()
    {
        $params = array(
            array(
                'name' => '注册',
                'status' => '',
                'url' => 'manage/register',
                'params' => array(
                    '名字'=>'username',
                    '手机号'=>'phone',
                    '密码'=>'password',
                    '简介'=>'summary'
                )
            ),
             array(
                'name'=>'用户登陆',
                'status'=>'返回json：登陆成功,返回用户信息，403用户名不存在，413表示密码错误，500表示登陆失败',
                'url' => 'adminuser/userlogin',
                'params' => array(
                    '手机号'=>'phone',
                    '密码'=>'password'
                )
            ),
            array(
                'name' => '删除视频',
                'status' => '',
                'url' => 'api/plan/delAllByPlanAndDay',
                'params' => array(
                    '计划id'=>'plan_id',
                    '当前计划的第几天'=>'day'
                )
            )
                
        );
    
        return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function newsAction()
    {
        $params = array(
            array(
					'name' => '资讯列表',
					'status' => 'title:标题，content:内容，image_url1:封面图',
					'url' => 'api/news/retrieveByFilter',
					'params' => array(
					                   '开始的条目'=>'start',
					                   '要加载的条目'=>'num'
					                 )
				),
            array(
                'name' => '资讯详情',
                'status' => '',
                'url' => 'api/news/info',
                'params' => array(
                    '资讯id'=>'news_id'
                )
            ),
            array(
                'name' => '添加资讯',
                'status' => '',
                'url' => 'api/news/add',
                'params' => array(
                    '标题'=>'title',
                    '内容'=>'content',
                    'image_url'=>'image_url'
                )
            ),
            array(
                'name' => '删除资讯',
                'status' => '',
                'url' => 'api/news/del',
                'params' => array(
                    '资讯id'=>'news_id'
                )
            )
                
        );
    
        return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function marketAction()
    {
        $params = array(
            array(
					'name' => '拍卖行情列表',
					'status' => 'title:标题，content:内容，image_url1:封面图',
					'url' => 'api/market/retrieveByFilter',
					'params' => array(
					                   '开始的条目'=>'start',
					                   '要加载的条目'=>'num'
					                 )
				),
            array(
                'name' => '拍卖行情详情',
                'status' => '',
                'url' => 'api/market/info',
                'params' => array(
                    '拍卖行情id'=>'market_id'
                )
            ),
            array(
                'name' => '添加拍卖行情',
                'status' => '',
                'url' => 'api/market/add',
                'params' => array(
                    '标题'=>'title',
                    '内容'=>'content',
                    'image_url'=>'image_url'
                )
            ),
            array(
                'name' => '删除拍卖行情',
                'status' => '',
                'url' => 'api/market/del',
                'params' => array(
                    '拍卖行情id'=>'market_id'
                )
            )
                
        );
    
        return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function preshowAction()
    {
        $params = array(
            array(
					'name' => '拍卖预展列表',
					'status' => 'title:标题，content:内容，image_url1:封面图',
					'url' => 'api/preshow/retrieveByFilter',
					'params' => array(
					                   '开始的条目'=>'start',
					                   '要加载的条目'=>'num'
					                 )
				),
            array(
                'name' => '拍卖预展详情',
                'status' => '',
                'url' => 'api/preshow/info',
                'params' => array(
                    '拍卖预展id'=>'preshow_id'
                )
            ),
            array(
                'name' => '添加拍卖预展',
                'status' => '',
                'url' => 'api/preshow/add',
                'params' => array(
                    '标题'=>'title',
                    '内容'=>'content',
                    '预展时间(时间戳)'=>'showtime',
                    '预展地址'=>'showaddress',
                    '拍卖时间(时间戳)'=>'auctiontime',
                    '拍卖地址'=>'auctionaddress'
                )
            ),
            array(
                'name' => '删除拍卖预展',
                'status' => '',
                'url' => 'api/preshow/del',
                'params' => array(
                    '拍卖预展id'=>'preshow_id'
                )
            )
                
        );
    
        return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function publishAction()
    {
        $params = array(
            array(
		        'name' => '类别列表',
		        'status' => '',
		        'url' => 'api/recommended/categoryList',
		        'params' => array(
		        )
		    ),
            array(
                'name' => '诉求类别',
                'status' => '',
                'url' => 'api/publish/appeal',
                'params' => array(
                )
            ),
            array(
                'name' => '二级诉求类别',
                'status' => '',
                'url' => 'api/publish/secondAppeal',
                'params' => array(
                    '一级诉求类别id'=>'appeal_id'
                )
            ),
            array(
                'name' => '添加二级诉求类别',
                'status' => '',
                'url' => 'api/publish/secondAppealAdd',
                'params' => array(
                    '一级诉求类别id'=>'appeal_id',
                    '二级诉求类别名称'=>'name'
                )
            ),
            array(
                'name' => '发布',
                'status' => '',
                'url' => 'api/publish/add',
                'params' => array(
                	'一级诉求类别id'=>'appeal_id',
                    '发布类别的id'=>'cid',
		            '列表页图片,默认第一张,选择缩略图'=>'image_url',
		            '图片的id拼成的字符串，例如2,3,4'=>'image_ids'
                		
                )
            ),
            array(
                'name' => '商家评价',
                'status' => '',
                'url' => 'api/publish/comment',
                'params' => array(
                    '发布的信息的id'=>'id',
                	'诉求的二级类别的id'=>'appeal_id'
                )
            ),
            array(
                'name' => '商家是否评价',
                'status' => '成功返回已经做过的评价',
                'url' => 'api/publish/commentOrNot',
                'params' => array(
                    '发布的信息的id'=>'id'
                )
            ),
            array(
                'name' => '我的发布',
                'status' => '',
                'url' => 'api/publish/my',
                'params' => array(
                    'start'=>'start',
                    'num'=>'num'
                )
            ),
            array(
                'name' => '商品的反馈num',
                'status' => '',
                'url' => 'api/publish/appealNum',
                'params' => array(
                    '商品的id'=>'id'
                )
            ),
            array(
                'name' => '所有的个人发布的商品',
                'status' => '',
                'url' => 'api/publish/publishAll',
                'params' => array(
                    'start'=>'start',
                    'num'=>'num'
                )
            ),
            array(
                'name' => '详情',
                'status' => '',
                'url' => 'api/publish/info',
                'params' => array(
                    'id'=>'id'
                )
            )
    
        );
    
        return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
    public function leavewordAction()
    {
    	$params = array(
    			array(
    					'name' => 'insert',
    					'status' => '',
    					'url' => 'api/leaveword/insert',
    					'params' => array(
    							'content'=>'content'
    					)
    			),
    			array(
    					'name' => 'retrieve',
    					'status' => 'retrieve',
    					'url' => 'api/leaveword/retrieve',
    					'params' => array(
    					)
    			)
    
    	);
    
    	return $this->render('YdzyTestBundle:Default:testcase.html.twig', array('params' => $params));
    }
	
	// for image upload only
	public function imageAction()
	{
		return $this->render('YdzyTestBundle:Default:upload.html.twig');
	}
	
	// for file upload only
	public function fileAction()
	{
		return $this->render('YdzyTestBundle:Default:fileupload.html.twig');
	}
	
}
