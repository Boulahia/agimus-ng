<?php

/**
* @ORM\Entity
* @ORM\Table(name="graphe")
*/
class Graphe {

    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $title;

    /**
    * @ORM\Column(type="string", length=250)
    */
    protected $url;

    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $description;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Graphe
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Graphe
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Graphe
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
      return $this->getTitle();
    }

    public function getCheckedUrl($from_time="", $to_time="") {
        //check if embed is in url
        $parsed_url = parse_url(str_replace('?','%3F',$this->url));
        
        //check port
        if(isset($parsed_url['port'])) unset($parsed_url['port']);

        //check embed
        $urlfragment = parse_url($this->url, PHP_URL_FRAGMENT);
        $parseurlfragment = parse_url($urlfragment);
        $urlqueryfragment = parse_url($urlfragment, PHP_URL_QUERY);

        //check time
        //_g=(time:(from:'2015-07-01',mode:absolute,to:'2015-07-30'))
        if($to_time=="") {
            $yesterday = mktime(0, 0, 0, date("m"), date("d")-1,   date("Y"));
            $to_time = date("Y-m-d", $yesterday);
        }

        if($from_time=="") {
            $lastmonth = mktime(0, 0, 0, date("m")-1, date("d")-1,   date("Y"));
            $from_time = date("Y-m-d",$lastmonth);
        }

        //$g = "(time:(from:'".$from_time."',mode:absolute,to:'".$to_time."'))";
        //$g = "(refreshInterval:(display:Off,pause:!f,section:0,value:0),time:(from:now-30d,mode:quick,to:now))";
        $g = "(refreshInterval:(display:Off,pause:!f,section:0,value:0),time:(from:'".$from_time."',mode:absolute,to:'".$to_time."'))";
        //$g="(refreshInterval:(display:Off,pause:!f,section:0,value:0),time:(from:'2015-09-04T22:00:00.000Z',mode:absolute,to:'2015-10-04T22:00:00.000Z'))";
        //timestamp:%5Bnow-2d%20TO%20now-1d%5D
        //$g="(refreshInterval:(display:Off,pause:!f,section:0,value:0),time:(from:'".$from_time."T22:00:00.000Z',mode:absolute,to:'".$to_time."T22:00:00.000Z'))";
        //http://agimus-ng.univ-lille1.fr:5601/#/visualize/edit/annuaire-nb_pers_affectations?embed&_g=(refreshInterval:(display:Off,pause:!f,section:0,value:0),time:(from:now-30d,mode:quick,to:now))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:ldap-stat%20AND%20attribut:supannEntiteAffectationPrincipale%20AND%20timestamp:%5Bnow-2d%20TO%20now-1d%5D')),vis:(aggs:!((id:'1',params:(field:count),schema:metric,type:max),(id:'2',params:(field:value,order:desc,orderBy:'1',size:20),schema:segment,type:terms)),listeners:(),params:(addLegend:!t,addTimeMarker:!f,addTooltip:!t,defaultYExtents:!f,mode:stacked,scale:linear,setYExtents:!f,shareYAxis:!t,times:!(),yAxis:()),type:histogram))

        parse_str($urlqueryfragment, $get_array);
        $a="";
        if(isset($get_array["_a"])) $a=$get_array["_a"];
        elseif(isset($get_array["amp;_a"])) $a=$get_array["amp;_a"];

        //preg_match('/timestamp:\[(.*) TO (.*)\]/', $a, $matches);

        if(preg_match('/timestamp:\[(.*) TO (.*)\]/', $a, $matches)==1) {
            $g="(refreshInterval:(display:Off,pause:!f,section:0,value:0),time:(from:".$matches[1].",mode:quick,to:".$matches[2]."))";
        }
        

        $new_query = "embed&_g=".$g."&_a=".$a;
        $parseurlfragment['query'] = $new_query;
        $urlfragment = $this->unparse_url($parseurlfragment);
        $parsed_url['fragment'] = $urlfragment;

        $new_url = $this->unparse_url($parsed_url);
        
        return $new_url;
    }

    function unparse_url($parsed_url) { 
      $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
      $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
      $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
      $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
      $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
      $pass     = ($user || $pass) ? "$pass@" : ''; 
      $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
      $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
      $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
      return "$scheme$user$pass$host$port$path$query$fragment"; 
    }

}
