<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContactRequest;
use App\Models\Contact;
use App\Repositories\API\ContactRepository;
use Validator;
use Illuminate\Http\Request;

class PhonebookController extends BaseController
{
    protected $contactRepository;

    public function __construct()
    {
        $this->contactRepository = app(ContactRepository::class);
    }

    public function contacts(Request $request)
    {

        $contacts = $this->contactRepository->getAllClient();
        //$clients = Client::all();
        return $this->sendResponse($contacts, 'Contacts Retrieved');
    }

    public function createContact(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|min:3|max:255',
            'note' => 'max:255',
            'phones' => 'array|required',
            'phones.*' => 'required|min:11|numeric',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contact = Contact::create($input);
        if($contact){
            $id = $contact->id;
            $update = $this->contactRepository->phonesSave($id, $input['phones']);
            if($update){
                return $this->sendResponse($contact, 'Контакт успешно сохранен');
            }else{
               return $this->sendError('Телефоны не могут быть сохранены.');
            }

        }
        return $this->sendError('Ошибка при сохранении');


    }

    public function singleContact($id){
        $contact = $this->contactRepository->getOneById($id);
        if (is_null($contact)) {
            return $this->sendError('Contact not found.');
        }
        return $this->sendResponse($contact, 'Contact retrieved successfully.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteContact($id)
    {
        $contact = Contact::find($id);
        if (is_null($contact)) {
            return $this->sendError('Contact not found.');
        }
        if($this->contactRepository->deletePhones($id)){
            $contact->delete();
            return $this->sendResponse($contact->toArray(), 'Contact deleted successfully.');
        }else{
            return $this->sendError('Contact not deleted.');
        }


    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateContact (Request $request, int $id){
        //dd($request);
        $input = $request->all();
        $contact = Contact::find($id);
        if (is_null($contact)) {
            return $this->sendError('Contact not found.');
        }
        $validator = Validator::make($input, [
            'name' => 'required|min:3|max:255',
            'note' => 'max:255',
            'phones' => 'array|required',
            'phones.*' => 'required|min:11|numeric',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contact->name = $input['name'];
        $contact->note = $input['note'];
        $contact->save();
        $phones = $this->contactRepository->phoneUpdate($id, $input['phones']);
        $contact->phones = $phones;

        return $this->sendResponse($contact->toArray(), 'Contact updated successfully.');
    }
}
