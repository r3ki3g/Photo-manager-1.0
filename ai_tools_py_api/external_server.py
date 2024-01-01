from mtcnn.mtcnn import MTCNN
from flask import Flask, request
from flask_cors import CORS


import cv2 as cv
import numpy as np
import matplotlib.pyplot as plt


import tensorflow as tf
import keras
import os
import random
import json


from keras_facenet import FaceNet
MyFaceNet = FaceNet()

detector = MTCNN()

JSONDIR = 'D:\ENTC\PROJECTS\Face Recognition Practice\json'


def get_full_path(path):
    return r'''D:/HTTP_WEB/realfeedServer/photo.manager/''' + path + '.jpeg'


def get_image_coll(imagepath):
    img = cv.imread(imagepath)
    assert img is not None
    imgRGB = cv.cvtColor(img, cv.COLOR_BGR2RGB)
    imgGray = cv.cvtColor(img, cv.COLOR_BGR2GRAY)
    return {"BGR": img, "RGB": imgRGB, "GRAY": imgGray}


with open(JSONDIR + r"\facefeature_db.json") as file:
    facefeature_db = json.load(file)

with open(JSONDIR + r"\people.json") as file:
    people = json.load(file)

allDP_embedding_tensor = np.array(facefeature_db['allDP_embedding_tensor'])
ownerIDs_of_allDP_embedding_tensor = facefeature_db['ownerIDs_of_allDP_embedding_tensor']


def getBestMatching_ownerId(queyFaceImgRGB):
    embeddings = MyFaceNet.embeddings(np.expand_dims(queyFaceImgRGB, axis=0)).T
    distances = np.linalg.norm((allDP_embedding_tensor - embeddings), axis=0)
    bestMatch = np.argmin(distances)
    return ownerIDs_of_allDP_embedding_tensor[bestMatch]


def getAllIdsOfPersonsInTheImage(queryImageRGB):
    allIdsOdPersons = []
    faces = detector.detect_faces(queryImageRGB)
    for face in faces:
        x, y, w, h = face['box']
        croppedFace = queryImageRGB[y:y+h, x:x+w, :]
        allIdsOdPersons.append(getBestMatching_ownerId(croppedFace))
    return allIdsOdPersons


def getNameListForIdList(idList):
    nameList = []
    for personId in idList:
        name = [person["name"]
                for person in people if person["id"] == personId][0]
        nameList.append(name)
    return nameList


def getPersonList_for_idList(idList):
    personList = []
    for personId in idList:
        person = [person for person in people if person["id"] == personId][0]
        personList.append(person)
    return personList


app = Flask(__name__)
CORS(app)


@app.route("/", methods=["GET", "POST"])
def homepage():
    photo_id = request.args.get('photoid')
    imgFullPath = get_full_path(photo_id)
    testImg = cv.imread(imgFullPath)
    assert testImg is not None
    testImg = cv.cvtColor(testImg, cv.COLOR_BGR2RGB)
    idList = getAllIdsOfPersonsInTheImage(testImg)
    personList = getPersonList_for_idList(idList)
    return json.dumps(personList)

    # return "hello world"


if __name__ == '__main__':
    app.run(debug=True, port=8081)
