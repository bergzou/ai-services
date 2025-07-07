<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Xbyter\ApolloClient\ApolloConfig;
use Xbyter\ApolloClient\ApolloClient;
use Xbyter\ApolloClient\ApolloConfigSync;
use Xbyter\ApolloClient\Handlers\ApolloEnvHandler;

class UpdateApolloEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateApolloEnv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '阿波罗配置同步';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{

            $apolloConfig = new ApolloConfig();
            $apolloConfig->configServerUrl = env('APOLLO_CONFIG_SERVER_URL');
            $apolloConfig->appId = env('APOLLO_APP_ID');
            $apolloConfig->cluster = env('APOLLO_CLUSTER');
            $apolloConfig->secret = env('APOLLO_SECRET');
            // 创建 Apollo 客户端对象
            // $client = new ApolloClient($apolloConfig);
            $envFilePath = base_path() .'/'.'.env';

            $apolloClient = new ApolloClient($apolloConfig);
            //指定需要修改的目录
            $handler = new ApolloEnvHandler($envFilePath);
            $sync = new ApolloConfigSync($apolloClient);
            $sync->addHandler(getenv('APOLLO_NAMESPACE'), $handler);
            $sync->force();
            //运行apollo
//            $sync->run('',120);
//            Log::channel('apollo')->info('env 文件已更新 '.$envFilePath);
        }catch(\Exception $e){
//            Log::channel('apollo')->info('env 文件已失败 '.$e->getMessage());
            echo 'env 文件已失败 '.$e->getMessage();
        }

    }
}
