<?php
namespace App\Repositories\API;

//use App\Models\Client;
use App\Repositories\CoreRepository;
use App\Models\Contact as Model;
use Illuminate\Support\Facades\DB;


class ContactRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Model::class;
    }

    public function getAllContact(){
        $contacts = $this->startCondition()
            ->leftjoin('phones', 'contacts.id', '=', 'phones.contact_id')
            ->select('contacts.*', 'phones.phone as phone', 'phones.id as phone_id')
            ->orderBy('contacts.name', 'ASC')
            ->orderBy('contacts.id', 'ASC')
            ->get();
        return $this->groupByContact($contacts);

    }

    public function phonesSave($id, $phones){
        $insertArray = [];
        foreach ($phones as $phone){
            $insertArray[] = ['contact_id' => $id, 'phone' => $phone];
        }
        $result = DB::table('phones')->insert($insertArray);
        return $result;

    }

    public function getOneById($id): array
    {

        $contact = $this->startCondition()
            ->leftjoin('phones', 'contacts.id', '=', 'phones.contact_id')
            ->select('contacts.*', 'phones.phone as phone', 'phones.id as phone_id')
            ->where('contacts.id', '=', $id)
            ->orderBy('contacts.id', 'ASC')
            ->get();

        return $this->groupByContact($contact);
    }

    public function groupByContact($contacts){
        $contactGroup = [];
        foreach ($contacts as $contact) {
            if (!isset($contactGroup[$contact->id])) {
                $contactGroup[$contact->id] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'note' => $contact->note,
                    'phones' => [0=> ['phone_id'=>$contact->phone_id, 'phone'=>$contact->phone]],
                ];
            }
            else {
                $contactGroup[$contact->id]['phones'][] = ['phone_id'=>$contact->phone_id, 'phone'=>$contact->phone];
            }
        }
        return array_values($contactGroup);
    }

    /**
     * Delete phones by id
     * @param  int  $id
     *  @return boolean $result
     */
    public function deletePhones($id){
        $result = DB::table('phones')
            ->where('contact_id','=', $id)
            ->delete();            ;
        return $result;

    }
    /**
     * Update phones by id
     * @param  int  $id
     * @param array $phones
     * @return array $phones
     */
    public function phoneUpdate($id, $phones){
        $this->deletePhones($id);
        $this->phonesSave($id, $phones);
        $result = DB::table('phones')
            ->select('id', 'phone')
            ->where('contact_id','=', $id)
            ->get();
        return $result;
    }

}
