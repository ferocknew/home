# coding=UTF-8  
import os
import Image

rootDir = '/Users/fuyingjun/Downloads'
def Test1(rootDir):
    for root, dirs, files in os.walk(rootDir):
        for filespath in files:
            print os.path.join(root, filespath)

Test1(rootDir)

def changImg(filePath, outFilePath):
    im = Image.open(filePath)
    (x, y) = im.size  # read image size
    x_s = 250  # define standard width
    y_s = y * x_s / x  # calc height based on standard width
    out = im.resize((x_s, y_s), Image.ANTIALIAS)  # resize image with high-quality
    out.save(outFilePath)
