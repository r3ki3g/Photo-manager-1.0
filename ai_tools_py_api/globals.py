

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

import secrets


# heavy libraries and initialization ************************************************

if 1:
    from mtcnn.mtcnn import MTCNN
    from keras_facenet import FaceNet
    detector = MTCNN()
    MyFaceNet = FaceNet()

# end : heavy libraries and initialization *********************************************


JSONDIR = r'D:\ENTC\PROJECTS\Face_Recognition_Practice\json'
IMAGEPATH = r'D:\HTTP_WEB\realfeedServer\photo.manager'


# database loadings
with open(JSONDIR + r"\facefeature_db.json") as file:
    facefeature_db = json.load(file)

with open(JSONDIR + r"\people.json") as file:
    people = json.load(file)

allDP_embedding_tensor = np.array(facefeature_db['allDP_embedding_tensor'])
ownerIDs_of_allDP_embedding_tensor = facefeature_db['ownerIDs_of_allDP_embedding_tensor']
