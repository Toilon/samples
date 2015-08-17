class Db_options_model extends CI_Model
{
    public function getDiintProminSessionProduction()
    {
        $session = $this->db->get_where('Site.dbo.db_options', ['s_key' => 'session'])->result_array();
        if (! empty($session)) {
            $session = $session[0]['s_value'];
            return $session;
        } else {
            throw new Exception("Diint production session not found in DB!");
        }
    }
}





