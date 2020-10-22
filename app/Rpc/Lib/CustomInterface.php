<?php


namespace App\Rpc\Lib;


interface CustomInterface
{
    /**
     * Notes: 登录
     *
     * @author: lujianjin
     * datetime: 2020/10/14 17:48
     * @param string $userName
     * @param string $passWord
     * @param string $token
     * @return mixed
     */
    public function login(string $userName, string $passWord, string $token);

    /**
     * Notes: 退出
     *
     * @author: lujianjin
     * datetime: 2020/10/14 17:48
     * @return mixed
     */
    public function logout();

    /**
     * Notes: 获取微医生信息
     *
     * @param string $uuid
     * @param array $column
     * @return mixed
     * @author: lujianjin
     * datetime: 2020/10/14 17:49
     */
    public function getCustom(string $uuid, array $column);

    /**
     * Notes: 根据旧的微医生ID获取微医生
     *
     * @param int $oldId
     * @param string $token
     * @param array $column
     * @return mixed
     * @author: lujianjin
     * datetime: 2020/10/14 17:50
     */
    public function getCustomById(int $oldId, string $token, array $column);

    /**
     * Notes: 创建微医生
     *
     * @param string $account
     * @param string $customName
     * @param string $password
     * @param string $oaAccount
     * @param string $phone
     * @param int $groupId
     * @return mixed
     * @author: lujianjin
     * datetime: 2020/10/14 17:52
     */
    public function createCustom(string $account, string $customName, string $password, string $oaAccount, string $phone, int $groupId);

    /**
     * Notes: 获取可分配微医生列表
     *
     * @author: lujianjin
     * datetime: 2020/10/14 17:54
     * @return mixed
     */
    public function getAvailableCustom(): array;

    /**
     * Notes: 更新当天进粉数
     *
     * @author: lujianjin
     * datetime: 2020/10/14 17:57
     * @return mixed
     */
    public function updateCurrentFensCount();

    /**
     * Notes: 分页获取微医生uuid
     *
     * @author: lujianjin
     * datetime: 2020/10/14 17:58
     * @return mixed
     */
    public function getCustomInfoPage(int $page, int $limit, array $condition = []): array;
}
