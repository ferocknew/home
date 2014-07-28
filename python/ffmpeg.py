# coding=UTF-8
import os
import sys
import commands

filePath = sys.argv[1]
def getFileList(rootDir):
    for root, dirs, files in os.walk(rootDir):
        for filespath in files:
            fileP = os.path.join(root, filespath)
            print fileP
            fileEx = fileP.split('.')[-1]
            fileName = filespath.split('.')[-2]
            newFileName = root + "/" + fileName + ".mp4"
            # print newFileName
            if fileEx=='flv':
                a, b = commands.getstatusoutput('~/Documents/tools/ffmpeg.dir/ffmpeg -i ' + fileP + ' -c:v copy -c:a aac -strict experimental -b:a 128k -ac 2 -scodec copy ' + newFileName)
                print b

getFileList(filePath)
