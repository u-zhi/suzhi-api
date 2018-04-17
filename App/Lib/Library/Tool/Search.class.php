<?php
/**
 * des:Search.class.php
 * User: liangbo
 * Date: 2017/11/14
 * Time: 下午2:42
 */
require_once("aliyunsearch/OpenSearch/Autoloader/Autoloader.php");

use OpenSearch\Client\OpenSearchClient;
use OpenSearch\Client\SearchClient;
use OpenSearch\Util\SearchParamsBuilder;

class Tool_Search
{
    protected $client = null;
    //替换对应的access key id
    protected static $accessKeyId = 'LTAIR7DpHy9MN9Nq';
//替换对应的access secret
    protected static $secret = 'JNUk4XB1URQCONGdzpUzJvS4oajv28';
//替换为对应区域api访问地址，可参考应用控制台,基本信息中api地址
    protected static $endPoint = 'http://opensearch-cn-shanghai.aliyuncs.com';//此处为公网地址
    protected static $innerPoint = 'http://intranet.opensearch-cn-shanghai.aliyuncs.com';//内网
//替换为应用名
    protected static $appName = 'suzhi_cv_searcher';
//替换为下拉提示名称
    protected static $suggestName = '<suggest name>';
//开启调试模式
    protected static $options = array('debug' => true);

    protected $occupation=null;

    protected $experience=null;

    protected $province_id=null;

    protected $city_id=null;

    protected $work_year=null;

    protected $wage=null;

    protected $olds=null;

    protected $sex=null;

    protected $college=null;

    protected $industry=null;

    protected $highest_degree=null;

    protected $keyword=null;

    protected $query=null;

    protected $filter=null;



    public function __construct()
    {
        if ($this->client == null) {
            if (isset($_SERVER['TP_ENV']) && $_SERVER['TP_ENV'] === 'development') {
                $host = self::$endPoint;
            } else {
                $host = self::$innerPoint;
            }
            $this->client = new OpenSearchClient(self::$accessKeyId, self::$secret, $host, self::$options);
        }
    }

    public function search()
    {
        $searchClient = new SearchClient($this->client);
// 实例化一个搜索参数类
        $params = new SearchParamsBuilder();
// 指定一个应用用于搜索
        $params->setAppName(self::$appName);
// 指定搜索关键词
        if($this->setQuery())
        {
            $params->setQuery($this->getQuery());
        }
        //设置config子句的start值
        $params->setStart(0);
//设置config子句的hit值
        $params->setHits(10000);
// 指定返回的搜索结果的格式为json
        $params->setFormat("fulljson");
        //设置文档过滤
        if($this->setFilter())
        {
            $params->setFilter($this->getFilter());

        }
//      $params->addSort('user_id', SearchParamsBuilder::SORT_DECREASE);
        $params->addSort('RANK', SearchParamsBuilder::SORT_DECREASE);
// 执行搜索，获取搜索结果
        $ret = $searchClient->execute($params->build())->result;
// 将json类型字符串解码
        return json_decode($ret,true);
    }

    /**
     * @return null
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * @param null $occupation
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;
    }

    /**
     * @return null
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * @param null $experience
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;
    }

    /**
     * @return null
     */
    public function getProvinceId()
    {
        return $this->province_id;
    }

    /**
     * @param null $province_id
     */
    public function setProvinceId($province_id)
    {
        $this->province_id = $province_id;
    }

    /**
     * @return null
     */
    public function getCityId()
    {
        return $this->city_id;
    }

    /**
     * @param null $city_id
     */
    public function setCityId($city_id)
    {
        $this->city_id = $city_id;
    }

    /**
     * @return null
     */
    public function getWorkYear()
    {
        return $this->work_year;
    }

    /**
     * @param null $work_year
     */
    public function setWorkYear($work_year)
    {
        $this->work_year = $work_year;
    }

    /**
     * @return null
     */
    public function getWage()
    {
        return $this->wage;
    }

    /**
     * @param null $wage
     */
    public function setWage($wage)
    {
        $this->wage = $wage;
    }

    /**
     * @return null
     */
    public function getOlds()
    {
        return $this->olds;
    }

    /**
     * @param null $olds
     */
    public function setOlds($olds)
    {
        $this->olds = $olds;
    }

    /**
     * @return null
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param null $sex
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    }

    /**
     * @return null
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @param null $college
     */
    public function setCollege($college)
    {
        $this->college = $college;
    }

    /**
     * @return null
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * @param null $industry
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;
    }

    /**
     * @return null
     */
    public function getHighestDegree()
    {
        return $this->highest_degree;
    }

    /**
     * @param null $highest_degree
     */
    public function setHighestDegree($highest_degree)
    {
        $this->highest_degree = $highest_degree;
    }

    /**
     * @return null
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param null $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @return null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param null $query
     * 设置query 拼凑项
     */
    public function setQuery()
    {
        $query="";
        if($this->getOccupation())
        {
            if(!empty($query))
                $query=$query.' AND '.$this->getOccupation();
            else
                $query=$this->getOccupation();
        }
        if($this->getKeyword())
        {
            if(!empty($query))
                $query=$query.' OR '.$this->getKeyword();
            else
                $query=$this->getKeyword();
        }
        if($this->getIndustry())
        {
            if(!empty($query))
                $query=$query.' AND '.$this->getIndustry();
            else
                $query=$this->getIndustry();
        }
        if($this->getExperience())
        {
            if(!empty($query))
                $query=$query.' AND '.$this->getExperience();
            else
                $query=$this->getExperience();
        }
        if($this->getProvinceId())
        {
            if(!empty($query))
                $query=$query.' AND '.$this->getProvinceId();
            else
                $query=$this->getProvinceId();
        }
        $this->query = $query;
        return $this->query;
    }

    /**
     * @return null
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param null $filter
     * 设置filter 拼凑项
     */
    public function setFilter()
    {
        $filter="";

        if($this->getWorkYear())
        {
            if(!empty($filter))
                $filter=$filter.' AND '.$this->getWorkYear();
            else
                $filter=$this->getWorkYear();
        }
        if($this->getCityId())
        {
            if(!empty($filter))
                $filter=$filter.' AND '.$this->getCityId();
            else
                $filter=$this->getCityId();
        }
        if($this->getSex())
        {
            if(!empty($filter))
                $filter=$filter.' AND '.$this->getSex();
            else
                $filter=$this->getSex();
        }
        if($this->getCollege()) {
            if(!empty($filter))
                $filter=$filter.' AND '.$this->getCollege();
            else
                $filter=$this->getCollege();
        }
        if($this->getOlds())
        {
            if(!empty($filter))
                $filter=$filter.' AND '.$this->getOlds();
            else
                $filter=$this->getOlds();
        }
        if($this->getWage())
        {
            if(!empty($filter))
                $filter=$filter.' AND '.$this->getWage();
            else
                $filter=$this->getWage();
        }
        $this->filter = $filter;

        return $filter;


    }




}