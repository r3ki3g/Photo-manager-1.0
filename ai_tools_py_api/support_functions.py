
from globals import *


def get_full_path(path):
    return r'''D:/HTTP_WEB/realfeedServer/photo.manager/''' + path + '.jpeg'


def get_image_coll(imagepath):
    img = cv.imread(imagepath)
    assert img is not None
    imgRGB = cv.cvtColor(img, cv.COLOR_BGR2RGB)
    imgGray = cv.cvtColor(img, cv.COLOR_BGR2GRAY)
    return {"BGR": img, "RGB": imgRGB, "GRAY": imgGray}


def getBestMatching_ownerId_and_confidenceInfo(queyFaceImgRGB, MTCNN_output):
    '''
    returns a dictionary containing ownerId and confidence info
    inputs :
          queyFaceImgRGB : query face (cropped according to MTCNN box)
          confidence_returned : use this to propagate
    '''

    global MyFaceNet
    global allDP_embedding_tensor
    global ownerIDs_of_allDP_embedding_tensor

    embeddings = MyFaceNet.embeddings(np.expand_dims(queyFaceImgRGB, axis=0)).T
    # start_time = time.time()
    distances = np.linalg.norm((allDP_embedding_tensor - embeddings), axis=0)
    bestMatch = np.argmin(distances)
    # end_time = time.time()
    # print("spent time: " ,end_time - start_time )
    outputDict = {
        "id": ownerIDs_of_allDP_embedding_tensor[bestMatch],
        "MTCNN_output": MTCNN_output,
        "distance_btw_queryFace_and_ownerFace": distances[bestMatch],
        "FaceNet_embeddings": embeddings.tolist()
    }

    return outputDict


def getAllPersons_ownerIDs_and_confInfo_inTheImage(queryImageRGB, faceConfThresh=0.9):
    '''
    return the owener ids and MTCNN info per each face found in the query image
        faces with less confident than threshold are ignored
    '''

    allIdsOfPersons = []
    faces = detector.detect_faces(queryImageRGB)
    for face in faces:
        # ignore if not confident as threshold
        if face["confidence"] < faceConfThresh:
            continue

        x, y, w, h = face['box']
        # MTCNN_conf = face['confidence']
        MTCNN_output = face

        croppedFace = queryImageRGB[y:y+h, x:x+w, :]
        ID_and_confInfo = getBestMatching_ownerId_and_confidenceInfo(
            croppedFace, MTCNN_output)
        allIdsOfPersons.append(ID_and_confInfo)
    return allIdsOfPersons


def insertNameTo_user_dict_list(dictList):
    '''
    returns nothing.
    mutates/inserts the user name if the user "id" found in the dict
    '''
    global people

    for personDict in dictList:
        name, dp = [(person["name"], person["dp"])
                    for person in people if person["id"] == personDict['id']][0]
        personDict["name"] = name
        personDict["dp"] = dp
    return None


def getPersonList_for_idList(idList):
    personList = []
    for personId in idList:
        person = [person for person in people if person["id"] == personId][0]
        personList.append(person)
    return personList


def generate_random_string(length):
    charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
    random_string = ''.join(secrets.choice(charset) for _ in range(length))
    return random_string


def expand_the_face_a_little(face_bounding_box, image_shape):
    EFECTIVE_EXPAND_RATIO_FOR_LARGE_DIM = 0.17

    x, y, w, h = face_bounding_box
    max_y = image_shape[0]
    max_x = image_shape[1]

    h_diff_side = int(h * EFECTIVE_EXPAND_RATIO_FOR_LARGE_DIM / 2)
    y_new = max(y - h_diff_side, 0)
    h_new = h + h_diff_side*2 + max(h_diff_side-y, 0)

    x_midpoint = int(x + w / 2)
    final_width = int(h * (1 + EFECTIVE_EXPAND_RATIO_FOR_LARGE_DIM))
    x_new = max(int(x_midpoint - final_width/2), 0)
    w_new = final_width + max(-int(x_midpoint - final_width/2), 0)

    # upper bound limits to consider
    y_new -= max((y_new + h_new) - max_y, 0)
    x_new -= max((x_new + w_new) - max_x, 0)

    return [x_new, y_new, w_new, h_new]
