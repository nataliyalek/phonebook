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
    /**
     * @OA\Get(
     *     path="/contacts",
     *     summary="Get all contacts",
     *     tags={"Contacts"},
     *     description="Get contact by id",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     * )
     */
    public function contacts(Request $request)
    {

        $contacts = $this->contactRepository->getAllContact();
        //$clients = Client::all();
        return $this->sendResponse($contacts, 'Contacts Retrieved');
    }

    /**
     * @OA\Post(
     *     path="/contacts",
     *     summary="Create contact",
     *     tags={"Contacts"},
     *     description="Create contact",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="note",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phones",
     *                     @OA\Schema(
     *                          type="array",
     *                          @OA\Items(
     *                              type="integer",
     *                          ),
     *                      ),
     *                 ),
     *
     *             )
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(ref="#/definitions/Contact"),
     *     ),
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/contacts/{id}",
     *     summary="Get contact by id",
     *     tags={"Contacts"},
     *     description="Get contact by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contact id",
     *         required=true,
     *           @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
      *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(ref="#/definitions/Contact"),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Contact is not found",
     *     )
     * )
     */
    public function singleContact($id){
        $contact = $this->contactRepository->getOneById($id);
        if (is_null($contact)) {
            return $this->sendError('Contact not found.');
        }
        return $this->sendResponse($contact, 'Contact retrieved successfully.');

    }


    /**
     * @OA\Delete(
     *     path="/contacts/{id}",
     *     summary="Delete contact by id",
     *     tags={"Contacts"},
     *     description="Delete contact by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Contact id",
     *         required=true,
     *         @OA\Schema(
     *           type="array",
     *           @OA\Items(type="integer"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(ref="#/definitions/Contact"),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Contact is not found",
     *     )
     * )
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
     * @OA\PUT(
     *     path="/contacts/{id}",
     *     summary="Update contact",
     *     tags={"Contacts"},
     *     description="Update contact",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contact id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Contact name",
     *         required=true,
     *         @OA\Schema(
     *          type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="note",
     *         in="query",
     *         description="Contact note",
     *         required=false,
     *         @OA\Schema(
     *          type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="phones",
     *         in="query",
     *         description="Contact phones",
     *         required=false,
     *         @OA\Schema(
     *           type="array",
     *           @OA\Items(
     *               type="integer",
     *           ),
     *        ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(ref="#/definitions/Contact"),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Contact is not found",
     *     )
     * )
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
