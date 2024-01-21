

from globals import *
from support_functions import *


app = Flask(__name__)
CORS(app)


@app.route("/", methods=["GET", "POST"])
def homepage():
    global detector
    global myFaceNet

    photo_id = request.args.get('photoid')
    if photo_id is None:
        return '{"status":"error"}'

    imgFullPath = get_full_path(photo_id)
    testImg = cv.imread(imgFullPath)
    if testImg is None:
        return '{"status":"error"}'

    testImg = cv.cvtColor(testImg, cv.COLOR_BGR2RGB)

    # image dimensions
    height = testImg.shape[0]
    width = testImg.shape[1]

    # idList = getAllIdsOfPersonsInTheImage(testImg)
    peopleList = getAllPersons_ownerIDs_and_confInfo_inTheImage(
        testImg)
    insertNameTo_user_dict_list(peopleList)

    response = {

        'peopleList': peopleList,
        'image_dims': {'height': height, 'width': width}

    }
    return json.dumps(response)

    # return "hello world"


@app.route("/update_face_database", methods=["POST", "GET"])
def update_face_database():
    global allDP_embedding_tensor
    global ownerIDs_of_allDP_embedding_tensor
    form_data = request.form
    if not('personId' in form_data and 'FaceNet_embeddings' in form_data):
        return "{'status':'error','error':'required data not included in the request'}"

    personId = form_data['personId']
    embeddings = json.loads(form_data['FaceNet_embeddings'])

    embeddings = np.array(embeddings)
    print("embeddings shapoe", embeddings.shape)
    # print('allDP_embedding_tensor.shape', allDP_embedding_tensor.shape)

    print("embeddings length", len(embeddings))

    allDP_embedding_tensor = np.concatenate(
        (allDP_embedding_tensor, embeddings), axis=1)
    ownerIDs_of_allDP_embedding_tensor.append(personId)

    # save the updated data to the json files
    facefeature_db['allDP_embedding_tensor'] = allDP_embedding_tensor.tolist()
    facefeature_db['ownerIDs_of_allDP_embedding_tensor'] = ownerIDs_of_allDP_embedding_tensor
    with open(JSONDIR + r"\facefeature_db.json", "w") as file:
        json.dump(facefeature_db, file)

    # * get cropped face - -> get face embeddings
    # * ownerIDs_of_allDP_embedding_tensor, allDP_embedding_tensor update simultaneously
    # *

    response = json.dumps({
        "status": "success"
    })

    return response


@app.route("/get_dp_display", methods=["POST", "GET"])
def get_dp_display():
    form_data = request.form
    if 'request_type' not in form_data:
        return "{'status':'error','error':'required \"request_type\" was not included in the request'}"

    if form_data['request_type'] == 'get_dpId_from_box':
        if not('face_bounding_box' in form_data and 'original_photo' in form_data):
            return "{'status':'error','error':'required data was not included in the request'}"

        face_bounding_box = json.loads(form_data['face_bounding_box'])
        original_photo = form_data['original_photo']
        image = cv.imread(get_full_path(original_photo))
        if image is None:
            return "{'status':'error','error':'Image was not found at server'}"

        # x, y, w, h = face_bounding_box
        x, y, w, h = expand_the_face_a_little(face_bounding_box, image.shape)
        dpCrop = image[y:y+h, x:x+w, :]

        dpPath = ''
        while True:
            dpPath = generate_random_string(64)
            if not os.path.exists(os.path.join(IMAGEPATH, dpPath + '.jpeg')):
                break

        cv.imwrite(os.path.join(IMAGEPATH, dpPath + '.jpeg'), dpCrop)

        response = {
            "status": "success",
            "dpPath": dpPath
        }

        return json.dumps(response)


if __name__ == '__main__':

    app.run(debug=True, port=8081)
