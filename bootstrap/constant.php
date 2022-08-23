<?php
// 常量定义

/**
 * 响应码
 */
const ERR_SUCCESS = 0; // 成功
const ERR_FAILED = 1; // 失败
const ERR_PARAM_ERR = 2; // 参数错误
const ERR_NOT_LOGIN = 3; // 登录失败
const ERR_USER_DISABLED = 4; // 用户已禁用
const ERR_USER_NOT_CERT = 5; // 未实名认证
const ERR_USER_NOT_TRANS_PASSWORD = 6; // 未设置支付密码
const ERR_USER_NOT_WITHDRAW_ACCOUNT = 7; // 未设置收款账号
const ERR_USER_NOT_REGISTER = 8; // 未注册
const ERR_USER_REGISTERED = 9; // 已注册
const ERR_EXPIRED = 10; // 已过期

/**
 * 用户状态
 */
const USER_STATUS_DEL = 1; // 已注销
const USER_STATUS_DISABLE = 2; // 已禁用
const USER_STATUS_ABLE = 3; // 正常


/**
 * 用户增值信息开启状态
 */
const USER_INFO_STATUS_DISABLE = 1; // 不开启
const USER_INFO_STATUS_ABLE = 2; // 开启


/**
 * 提现账户类型
 */
const WITHDRAW_ACCOUNT_ALIPAY = 1; // 支付宝
const WITHDRAW_ACCOUNT_BANK = 2; // 银行卡

/**
 * 提现审核状态
 */
const WITHDRAW_STATUS_PENDING = 1; // 待审核
const WITHDRAW_STATUS_CANCEL = 2; // 取消
const WITHDRAW_STATUS_ABLE = 3; // 通过
const WITHDRAW_STATUS_DISABLE = 4; // 拒绝

/**
 * 用户钱包记录类型
 */
const USER_WALLET_RECORD_RECHARGE = 1; // 充值
const USER_WALLET_RECORD_WITHDRAW = 2; // 提现
const USER_WALLET_RECORD_DOWNLOAD_REWARD = 4; // 下载奖励
const USER_WALLET_RECORD_INVITE_REWARD = 5; // 邀请奖励

/**
 * 支付结果
 */
const PAYMENT_STATUS_PENDING = 1; // 待支付
const PAYMENT_STATUS_ABLE = 2; // 已支付

/**
 * 支付方法
 */
const PAYMENT_METHOD_WECHAT = 1; // 微信支付
const PAYMENT_METHOD_ALIPAY = 2; // 支付宝支付
const PAYMENT_METHOD_REWARD = 3; // 平台奖励
const PAYMENT_METHOD_BALANCE = 4; // 余额


/**
 * 服务商状态
 */
const PROVIDER_STATUS_DISABLE = 1; // 已禁用
const PROVIDER_STATUS_ABLE = 2; // 正常

/**
 * 服务商认证状态
 */
const PROVIDER_INFO_STATUS_NOT = 0; // 未提交
const PROVIDER_INFO_STATUS_PENDING = 1; // 待审核
const PROVIDER_INFO_STATUS_ABLE = 2; // 通过
const PROVIDER_INFO_STATUS_DISABLE = 3; // 拒绝

/**
 * APP状态
 */
const APP_STATUS_DISABLE = 1;// 已下架
const APP_STATUS_ABLE = 2; // 已上架

/**
 * APP版本状态
 */
const APP_VERSION_STATUS_PENDING = 1;// 待审核
const APP_VERSION_STATUS_ABLE = 2; // 通过
const APP_VERSION_STATUS_DISABLE = 3;// 拒绝

