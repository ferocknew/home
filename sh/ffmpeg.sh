#!/bin/sh

while getopts "a:bc" arg #选项后面的冒号表示该选项需要参数
do
        case $arg in
             a)
                echo "$OPTARG" #参数存在$OPTARG中
                cd $OPTARG
                for filename in `ls`
                do
                    # echo $filename
                    
                    sn=${filename##*.}
                    # echo $sn

                    if [[ $sn == "flv" ]]; then
                        echo $filename
                        echo ${filename%.*}
                    fi
                done
                ;;
             ?)  #当有不认识的选项的时候arg为?
            echo "unkonw argument"
        exit 1
        ;;
        esac
done
