using EWorldPathologyIndexModel;
using Newtonsoft.Json;
using OpenMcdf;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Xml;

namespace EWorldPathologyIndexCreator
{
    internal class MoticIndexCreator : IndexCreatorBase
    {
        private const string NAME_PROPERTY = "Property";

        private const string NAME_DSIO = "DSI0";

        private const string NAME_MoticDigitalSlideImage = "MoticDigitalSlideImage";

        private CompoundFile cf = null;

        public override byte[] CreateIndex(string slidePath)
        {
            byte[] content = null;

            this.cf = new CompoundFile(slidePath);

            // 先获取属性
            this.GetProperty();

            // 获取层信息
            this.GetLayerInfo();

            // 获取Tile信息
            this.GetTileInfo();

            // 获取Label
            this.GetLabel();

            // 获取AssociateImage
            this.GetAssociateImage();

            string strJson = JsonConvert.SerializeObject(this.index);
            content = Encoding.UTF8.GetBytes(strJson);

            return content;
        }

        private void GetProperty()
        {
            var stream = this.cf.RootStorage.GetStream(NAME_PROPERTY);
            if (stream == null)
            {
                throw new Exception("[麦克奥迪]无法获取切片的属性");
            }

            byte[] btProperty = stream.GetData();

            this.index.Property = new EWorldPathologyIndexModel.PathologyProperty();

            // XML 解析
            XmlDocument doc = new XmlDocument();
            using (MemoryStream ms = new MemoryStream(btProperty))
            {
                doc.Load(ms);
                XmlNode ndProperty = doc.SelectSingleNode("Property");
                if (ndProperty == null)
                {
                    throw new Exception("[麦克奥迪]获取Property节点失败");
                }

                XmlNode ndTemp = ndProperty.SelectSingleNode("SlideOrient");
                if (ndTemp != null)
                {
                    this.index.Property.SlideOrient = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("ScanObjective");
                if (ndTemp != null)
                {
                    this.index.Property.ScanObjective = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("Scale");
                if (ndTemp != null)
                {
                    this.index.Property.Scale = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("ScanAreaX");
                if (ndTemp != null)
                {
                    this.index.Property.ScanAreaX = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("ScanAreaY");
                if (ndTemp != null)
                {
                    this.index.Property.ScanAreaY = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("ScanAreaWidth");
                if (ndTemp != null)
                {
                    this.index.Property.ScanAreaWidth = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("ScanAreaHeight");
                if (ndTemp != null)
                {
                    this.index.Property.ScanAreaHeight = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("HorzAngle");
                if (ndTemp != null)
                {
                    this.index.Property.HorzAngle = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("VertAngle");
                if (ndTemp != null)
                {
                    this.index.Property.VertAngle = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("DeltaX");
                if (ndTemp != null)
                {
                    this.index.Property.DeltaX = ndTemp.Attributes["value"].Value.ToString();
                }

                ndTemp = ndProperty.SelectSingleNode("DeltaY");
                if (ndTemp != null)
                {
                    this.index.Property.DeltaY = ndTemp.Attributes["value"].Value.ToString();
                }
            }
        }

        private void GetLayerInfo()
        {
            var dsio = this.cf.RootStorage.GetStorage(NAME_DSIO);
            if (dsio == null)
            {
                throw new Exception("[麦克奥迪]不存在DSI0仓库");
            }

            var stream = dsio.GetStream(NAME_MoticDigitalSlideImage);
            if (stream == null)
            {
                throw new Exception("[麦克奥迪]获取MoticDigitalSlideImage内容失败");
            }

            var content = stream.GetData();
            using (MemoryStream ms = new MemoryStream(content))
            {
                XmlDocument doc = new XmlDocument();
                doc.Load(ms);

                var ndImageMatrix = doc.SelectSingleNode("MoticDigitalSlideImage/ImageMatrix");
                if (ndImageMatrix == null)
                {
                    throw new Exception("[麦克奥迪]不存在ImageMatrix节点无法获取Layer信息");
                }

                int nTemp = -1;

                XmlNode ndTemp = ndImageMatrix.SelectSingleNode("Width");
                if (ndTemp != null)
                {
                    string strTemp = ndTemp.Attributes["value"].Value.ToString();
                    int.TryParse(strTemp, out nTemp);
                    this.index.Property.SlideWidth = nTemp;
                }

                ndTemp = ndImageMatrix.SelectSingleNode("Height");
                if (ndTemp != null)
                {
                    string strTemp = ndTemp.Attributes["value"].Value.ToString();
                    int.TryParse(strTemp, out nTemp);
                    this.index.Property.SlideHeight = nTemp;
                }

                ndTemp = ndImageMatrix.SelectSingleNode("CellWidth");
                if (ndTemp != null)
                {
                    string strTemp = ndTemp.Attributes["value"].Value.ToString();
                    int.TryParse(strTemp, out nTemp);
                    this.index.Property.TileWidth = nTemp;
                }

                ndTemp = ndImageMatrix.SelectSingleNode("CellHeight");
                if (ndTemp != null)
                {
                    string strTemp = ndTemp.Attributes["value"].Value.ToString();
                    int.TryParse(strTemp, out nTemp);
                    this.index.Property.TileHeight = nTemp;
                }

                ndTemp = ndImageMatrix.SelectSingleNode("LayerCount");
                if (ndTemp != null)
                {
                    string strTemp = ndTemp.Attributes["value"].Value.ToString();
                    int.TryParse(strTemp, out nTemp);
                    this.index.Property.LayerCount = nTemp;
                }

                this.index.LayerList = new List<PathologyLayer>();
                string strLayerNameTmp = "Layer";
                for (int nIndex = 0; nIndex < this.index.Property.LayerCount; ++nIndex)
                {
                    PathologyLayer pl = new PathologyLayer();
                    pl.LayerIndex = nIndex;

                    var ndLayer = ndImageMatrix.SelectSingleNode(strLayerNameTmp + nIndex.ToString());
                    if (ndLayer == null)
                    {
                        throw new Exception("[麦克奥迪]第" + nIndex.ToString() + "层信息缺失");
                    }

                    ndTemp = ndLayer.SelectSingleNode("Rows");
                    if (ndTemp != null)
                    {
                        string strTemp = ndTemp.Attributes["value"].Value.ToString();
                        int.TryParse(strTemp, out nTemp);
                        pl.Rows = nTemp;
                    }

                    ndTemp = ndLayer.SelectSingleNode("Cols");
                    if (ndTemp != null)
                    {
                        string strTemp = ndTemp.Attributes["value"].Value.ToString();
                        int.TryParse(strTemp, out nTemp);
                        pl.Cols = nTemp;
                    }

                    ndTemp = ndLayer.SelectSingleNode("Scale");
                    pl.Scale = ndTemp.Attributes["value"].Value.ToString();

                    pl.TileList = new List<PathologyTileIndex>();
                    this.index.LayerList.Add(pl);
                }
            }
        }

        private void GetTileInfo()
        {
            var dsio = this.cf.RootStorage.GetStorage(NAME_DSIO);

            for (int nIndex = 0; nIndex < this.index.LayerList.Count; ++nIndex)
            {
                string strScale = this.index.LayerList[nIndex].Scale;

                var scaleStorage = dsio.GetStorage(strScale);
                scaleStorage.VisitEntries((CFItem target) =>
                {
                    if (target.IsStream)
                    {
                        var cFStream = target as CFStream;
                        var strNames = cFStream.Name.Split('_');

                        PathologyTileIndex pt = new PathologyTileIndex();
                        pt.RowIndex = int.Parse(strNames[0]);
                        pt.ColIndex = int.Parse(strNames[1]);
                        pt.Position = cFStream.GetSourcePosition();
                        pt.Length = cFStream.Size;

                        this.index.LayerList[nIndex].TileList.Add(pt);
                    }
                }, false);
            }
        }

        private void GetLabel()
        {
            var stream = this.cf.RootStorage.GetStream("Label");
            if (stream == null)
            {
                throw new Exception("[麦克奥迪]获取标签图失败");
            }

            this.index.LabelList = new List<PathologyAssociateImage>();
            PathologyAssociateImage image = new PathologyAssociateImage();
            image.Name = stream.Name;
            image.Position = stream.GetSourcePosition();
            image.Length = stream.Size;
            this.index.LabelList.Add(image);
        }

        private void GetAssociateImage()
        {
            var stream = this.cf.RootStorage.GetStream("Macro");
            if (stream == null)
            {
                throw new Exception("[麦克奥迪]获取Macro标签图失败");
            }

            this.index.AssociateImageList = new List<PathologyAssociateImage>();
            PathologyAssociateImage image = new PathologyAssociateImage();
            image.Name = stream.Name;
            image.Position = stream.GetSourcePosition();
            image.Length = stream.Size;
            this.index.AssociateImageList.Add(image);
        }
    }
}
